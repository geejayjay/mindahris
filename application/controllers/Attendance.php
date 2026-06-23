<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
		
	require(APPPATH."libraries/phpmailer/Exception.php");
	require(APPPATH."libraries/phpmailer/PHPMailer.php");
	require(APPPATH."libraries/phpmailer/SMTP.php");
			
	Class Attendance extends CI_Controller{
		

		public function __construct(){
			parent::__construct();
			$this->load->model('admin_model');

			$this->load->model('attendance_model');


		}

		public function index(){
			
		}

		public function importdata(){


			if($this->session->userdata('is_logged_in')!=TRUE){
				  redirect('/accounts/login/', 'refresh');
			}else{

			$getareas = $this->admin_model->getareas();

			$data['title'] = '| Import Timelogs';
			$data['areas'] = $getareas;
			$data['main_content'] = 'hrmis/attendance/import_attendance_view';
			$this->load->view('hrmis/admin_view',$data);


			}

		}
		
		function testdata() {
			//include(APPPATH."libraries/zklib/zklib.php");
		//	$zk   		  = new ZKLib("192.168.1.13", 4370);
		    
			include(APPPATH."libraries/newzk/zklibrary.php");
			$zk   		  = new ZKLibrary("192.168.1.201", 4370,"TCP");
			
			$zk->sec  = 5000;
			$zk->usec = 5000;
			
			// $getemployees = $this->admin_model->getemployees();
			$pass 		  = null;
			
			$area_id = 1;
			
			// $ret	 = $zk->connect();
			
			$zk->connect();
			$zk->disableDevice();
			$users = $zk->getUser();
			var_dump($users); 
			return;
			// $ret = $zk->connect();	
				
				    sleep(1);	
					if ( $ret ){
						
						$zk->disableDevice();
							sleep(1);
                             $device_info = array('version' 	   => $zk->version(), 
                             					  'osversion' 	   => $zk->osversion(), 
                             					  'platform' 	   => $zk->platform(), 
                             					  'firmware' 	   => $zk->fmVersion(), 
                             					  'workcode'       => $zk->workCode(), 
                             					  'ssr' 		   => $zk->ssr(), 
                             					  'pinWidth' 	   => $zk->pinWidth(),
                             					  'faceFunctionOn' => $zk->faceFunctionOn(),
                             					  'serialNumber'   => $zk->serialNumber(),
                             					  'deviceName'     => $zk->deviceName(),
                             					  'getTime' 	   => $zk->getTime()
                             					  );
					
							$attendance = $zk->getAttendance();
						
					//	var_dump($attendance); return;
						
				            sleep(1);
							
				            while( list($idx, $attendancedata) = each($attendance) ){
								
				                if ( $attendancedata[2] == 01 ){
				                    $status = 'C/Out';
				                }
				                else
				                {
				                    $status = 'C/In';
				                }
								
				                //if(strtotime($attendancedata[3]) > strtotime($last_update)){
		
				                	$checktime =  date( "n/j/Y", strtotime( $attendancedata[3] ) ).' '.date( "g:i A", strtotime( $attendancedata[3] ) );
										
										foreach ($getemployees as $rr) {
											//$data[] = substr($attendancedata[1],1, strlen($attendancedata[1])) . " = " . $rr->biometric_id;
											// mark 4.1
											if($rr->biometric_id == substr($attendancedata[1],1, strlen($attendancedata[1])) &&  $rr->area_id == $area_id){	
												$fullname = $rr->f_name;
												$data[] = array("index" 		=> $idx,
																"UID" 			=> $attendancedata[0],
																"biometric_id"  => intval(substr($attendancedata[1],1, strlen($attendancedata[1]))),
																"status" 		=> $status,
																"checktime" 	=> $checktime,
																"fullname" 		=> $fullname,
																"dummykey" 		=> "dummyvalue"); 
											}
											
										}
										
				                // }
								
				            }
						
				        $zk->enableDevice();
					//	sleep(1);
						
				        sleep(1);
				        $zk->disconnect();
						
						$pass = array('data' => $data , 'device_info' => $device_info);
	
					} else {
						$pass = false;
					}
				
				echo json_encode($pass);
		}

		function syncbiometricdatalog(){

				$this->load->model("v2main/Globalproc");
				
				$info 		  = $this->input->post('info');
				
				$getemployees = $this->admin_model->getemployees();
												
				$area_code 	  = $info['area_code'];

				$area	 	  = $this->Globalproc->get_details_from_table("areas",["area_code"=>$area_code],["area_id","ipaddress"]);

				$area_id      = $area['area_id'];
				$ip   		  = $area['ipaddress'];
			
				$last_update  = $info['last_update'];
				
			//	$ip  = "124.107.247.139";
			    include(APPPATH."libraries/zklib/zklib.php");
				$zk   		  = new ZKLib("{$ip}", 4370);
				
			//	$zk->connect();
			//	var_dump($zk->getAttendance());
			//	$zk = new ZKLib("192.168.1.13", 4370); // davao  
			//	$zk = new ZKLib("124.107.247.139", 4370); // zambo
			//	$zk = new ZKLib("122.52.145.16", 4370); // koro
			//	$zk = new ZKLib("56.69.151.11", 4370); // butuan
				
				
				$pass = null;
				
				$ret = $zk->connect();

				$ret = $zk->connect();	
				    sleep(1);	
					if ( $ret ){
						
						$zk->disableDevice();
							sleep(1);
                             $device_info = array('version' 	   => $zk->version(), 
                             					  'osversion' 	   => $zk->osversion(), 
                             					  'platform' 	   => $zk->platform(), 
                             					  'firmware' 	   => $zk->fmVersion(), 
                             					  'workcode'       => $zk->workCode(), 
                             					  'ssr' 		   => $zk->ssr(), 
                             					  'pinWidth' 	   => $zk->pinWidth(),
                             					  'faceFunctionOn' => $zk->faceFunctionOn(),
                             					  'serialNumber'   => $zk->serialNumber(),
                             					  'deviceName'     => $zk->deviceName(),
                             					  'getTime' 	   => $zk->getTime()
                             					  );
					
							$attendance = $zk->getAttendance();

						
				            sleep(1);
							
				            while(list($idx, $attendancedata) = each($attendance)){
								
				                if ( $attendancedata[2] == 01 ){
				                    $status = 'C/Out';
				                }
				                else
				                {
				                    $status = 'C/In';
				                }
								
				                if(strtotime($attendancedata[3]) > strtotime($last_update)){
		
				                	$checktime =  date( "n/j/Y", strtotime( $attendancedata[3] ) ).' '.date( "g:i A", strtotime( $attendancedata[3] ) );
										
										foreach ($getemployees as $rr) {
											//$data[] = substr($attendancedata[1],1, strlen($attendancedata[1])) . " = " . $rr->biometric_id;
											// mark 4.1
											if($rr->biometric_id == substr($attendancedata[1],1, strlen($attendancedata[1])) &&  $rr->area_id == $area_id){	
												$fullname = $rr->f_name;
												$data[] = array("index" => $idx,
																"UID" => $attendancedata[0],
																"biometric_id" => intval(substr($attendancedata[1],1, strlen($attendancedata[1]))),
																"status" => $status,
																"checktime" => $checktime,
																"fullname" => $fullname,
																"dummykey" => "dummyvalue"); 
											}
											
										}
										
				                }
								
				          }
						
						// clear attendance here
						//	$zk->clearAttendance();
						// end clearattendance
						
				        $zk->enableDevice();
					//	sleep(1);
						
				        sleep(1);
				        $zk->disconnect();
						
						$pass = array('data' => $data , 'device_info' => $device_info);
	
					} else {
						$pass = false;
					}

				echo json_encode($pass);
			
		}
		
		function clearbio() {
			// area code
				// ip address
			
			#### check if user is admin
				// if user is admin
			
			
		}
		
		function cronjobsyncbio(){

				$getemployees = $this->admin_model->getemployees();
				
				$area_id = 1; // mark 1
				$latest_update = $this->attendance_model->get_attemndance_logs_last_update($area_id = 1);
				$last_update = $latest_update[0]->last_update;

			    include(APPPATH."libraries/zklib/zklib.php");
   			    $zk = new ZKLib("192.168.1.13", 4370);


   			     $ret = $zk->connect();
				    sleep(1);
					if ( $ret ){

							$zk->disableDevice();
							sleep(1);

                             $device_info = array('version' => $zk->version(), 
                             					  'osversion' => $zk->osversion(), 
                             					  'platform' => $zk->platform(), 
                             					  'firmware' => $zk->fmVersion(), 
                             					  'workcode' => $zk->workCode(), 
                             					  'ssr' => $zk->ssr(), 
                             					  'pinWidth' => $zk->pinWidth(),
                             					  'faceFunctionOn' => $zk->faceFunctionOn(),
                             					  'serialNumber' => $zk->serialNumber(),
                             					  'deviceName' => $zk->deviceName(),
                             					  'getTime' => $zk->getTime()
                             					  );


							$attendance = $zk->getAttendance();
				            sleep(1);
				            while(list($idx, $attendancedata) = each($attendance)){
				                if ( $attendancedata[2] == 01 ){
				                    $status = 'C/Out';
				                }
				                else
				                {
				                    $status = 'C/In';
				                }
				         	  	

				                if(strtotime($attendancedata[3]) > strtotime($last_update)){

				                		$checktime =  date( "n/j/Y", strtotime( $attendancedata[3] ) ).' '.date( "g:i A", strtotime( $attendancedata[3] ) );
										foreach ($getemployees as $rr) {

											if($rr->biometric_id == $attendancedata[1] && $rr->area_id == $area_id){
												$fullname = $rr->f_name;
												$data[] = array('index' => $idx , 'UID' => $attendancedata[0] , 'biometric_id' => $attendancedata[1] ,  'status' => $status , 'checktime' => $checktime  , 'fullname' => $fullname); 
											} 										
										
										} 
				                 }

				          }

						$fieldmap = array();

						$fieldmap[0]['checkinout'] = 'biometric_id';
						$fieldmap[0]['csv'] = 'biometric_id';
						$fieldmap[1]['checkinout'] = 'checktime';
						$fieldmap[1]['csv'] = 'checktime';
						$fieldmap[2]['checkinout'] = 'checktype';
						$fieldmap[2]['csv'] = 'status';

						$insertattendancelog = $this->admin_model->insertattendancelogv2($fieldmap , $data , 1);	
						if($insertattendancelog){
							echo 'yehey';
						}
				          

				        $zk->enableDevice();
				        sleep(1);
				        $zk->disconnect();
				   

					}


		}




		function save_sync_data_log(){
				$this->load->model("v2main/Globalproc");
				
				$info 	   = $this->input->post('info_a');
				$data_1    = json_decode($info);
				
				$data  	   = (array) $data_1->data;
				
				
				// get area code from the url
				$area_code = $this->input->get("area_code");
			
			
				$area_id   = $this->Globalproc->get_details_from_table("areas",["area_code"=>$area_code],["area_id","ipaddress"]);
				$area_id   = $area_id['area_id'];
				
				$fieldmap = array();

				$fieldmap[0]['checkinout'] = "biometric_id";
				$fieldmap[0]['csv'] 	   = "biometric_id";
				$fieldmap[1]['checkinout'] = "checktime";
				$fieldmap[1]['csv']		   = "checktime";
				$fieldmap[2]['checkinout'] = "checktype";
				$fieldmap[2]['csv'] 	   = "status";
				
				
				$insertattendancelog = $this->admin_model->insertattendancelogv2($fieldmap , $data , $area_id);	
				
				//echo json_encode($insertattendancelog); // mark 4
				
				if($insertattendancelog){
					$latest_update = $this->attendance_model->get_attemndance_logs_last_update($area_id);
					echo json_encode($latest_update[0]->last_update);
				}
				
		}



		function get_attendance_logs(){
			$info = $this->input->post('info');

				$getAttendancelogs = $this->attendance_model->getattendancelogs();

				$data = [];

				if($getAttendancelogs){

					foreach ($getAttendancelogs as $key => $value) {
						$data[] = array('fullname' => $value->f_name , 'checktime' => $value->checktime , 'status' => $value->checktype);
					}
					
				}

				echo json_encode($data);
				
		}

		function uploadattachment($type){

			$checkFilenames = [];

			$success = 0;

				for($i=0; $i<count($_FILES['file']['name']); $i++){
				    $dir_path = $_SERVER['DOCUMENT_ROOT']."/assets/attachments/";
				    if (!is_dir($dir_path)) {
				        mkdir($dir_path, 0777, true);
				    }
				    $ext = explode('.', basename( $_FILES['file']['name'][$i]));
				    $new_File_name = md5(uniqid()) . "." . $ext[count($ext)-1]; 
				    $target_path = $dir_path . $new_File_name;

				    if(move_uploaded_file($_FILES['file']['tmp_name'][$i], $target_path)) {

				    	$checkFilenames[] = array($new_File_name);
				    	$success = 1;
				       
				    } else{
				        log_message('error', '[UPLOAD ATTACHMENT] Failed to move uploaded file to: ' . $target_path);
				    }
				}



				 echo json_encode(array('filenames' => $checkFilenames , 'success' => $success));	

		}	


		function uploadattendance(){
		    
	        $status = 'error';
	        $msg = 'No file uploaded or file upload error.';
	        $csv = array(null, null);

	        $config['allowed_types'] = 'csv';
	        $config['upload_path'] = $_SERVER['DOCUMENT_ROOT'].'/assets/import/';
	        $config['max_size']	= '9000000';
	        $config['file_name'] = 'att_upload' . time();

	        if (!is_dir($config['upload_path'])) {
	            mkdir($config['upload_path'], 0777, true);
	        }

	        $this->load->library('upload', $config);

	        if (empty($_FILES)) {
	            $msg = 'No file was submitted for upload.';
	            log_message('error', '[UPLOAD ATTENDANCE] Upload failed: ' . $msg);
	            echo json_encode(array('status' => 'error', 'message' => $msg));
	            return;
	        }

	        foreach($_FILES as $field => $file)
	        {
	            if($file['error'] == 0)
	            {
	                if ($this->upload->do_upload($field))
	                {
	                    $data = $this->upload->data();

	                    $status = 'success';
	                    $msg = $data['file_name'];

	                    $csv = $this->ImportCSV2Array($data['full_path']);
	                }
	                else
	                {
	                    $status = 'error';
	                    $msg = $this->upload->display_errors('', '');
	                }
	            }
	            else
	            {
	                $status = 'error';
	                switch ($file['error']) {
	                    case UPLOAD_ERR_INI_SIZE:
	                    case UPLOAD_ERR_FORM_SIZE:
	                        $msg = 'The uploaded file exceeds the maximum allowed size.';
	                        break;
	                    case UPLOAD_ERR_PARTIAL:
	                        $msg = 'The file was only partially uploaded.';
	                        break;
	                    case UPLOAD_ERR_NO_FILE:
	                        $msg = 'No file was uploaded.';
	                        break;
	                    case UPLOAD_ERR_NO_TMP_DIR:
	                        $msg = 'Missing temporary folder on server.';
	                        break;
	                    case UPLOAD_ERR_CANT_WRITE:
	                        $msg = 'Failed to write file to disk.';
	                        break;
	                    case UPLOAD_ERR_EXTENSION:
	                        $msg = 'A PHP extension stopped the file upload.';
	                        break;
	                    default:
	                        $msg = 'Unknown upload error (code: ' . $file['error'] . ').';
	                        break;
	                }
	            }
	        }

	        if ($status === 'error') {
	            log_message('error', '[UPLOAD ATTENDANCE] Upload failed: ' . $msg);
	            echo json_encode(array('status' => 'error', 'message' => $msg));
	            return;
	        }

	        if (empty($csv[0]) || empty($csv[1])) {
	            $msg = 'The uploaded CSV file is empty or has invalid formatting.';
	            log_message('error', '[UPLOAD ATTENDANCE] CSV parse failed: ' . $msg);
	            echo json_encode(array('status' => 'error', 'message' => $msg));
	            return;
	        }

	      $getcheckinoutfields = $this->admin_model->getcheckinoutfields();

	      foreach($getcheckinoutfields as $row){
	      		$columns[] = $row->columns;
	      }



	      echo json_encode(array('csvfields'=> $csv[0], 'data' => $csv[1] , 'checkinoutfields' => $getcheckinoutfields));

	      						/* csv fields            csv uploaded data         checkinout fields from db */

		 }	



		 function uploadattachments(){

			$config['allowed_types'] = 'gif|jpg|png';
	        $config['upload_path'] = './assets/attachments/';
	        $config['max_size']	= '9000000';
	        $config['file_name'] = 'att_upload' . time(); 	

	        echo json_encode('success');

		 }



		 function ImportCSV2Array($filename)
		 {
			    $row = 0;
			    $col = 0;
			    $fields = array();
			    $results = array();
			 
			    $handle = @fopen($filename, "r");
			    if ($handle) 
			    {
			        while (($row = fgetcsv($handle, 4096)) !== false) 
			        {
			            if (empty($fields)) 
			            {
			                $fields = $row;
			                continue;
			            }
			 
			            foreach ($row as $k=>$value) 
			            {
			                if (isset($fields[$k])) {
			                    $results[$col][$fields[$k]] =  htmlentities($value, ENT_QUOTES, 'UTF-8');
			                }
			            }
			            $col++;
			            unset($row);
			        }
			        if (!feof($handle)) 
			        {
			            log_message('error', '[UPLOAD ATTENDANCE] CSV Import: unexpected fgetcsv() fail on file ' . $filename);
			        }
			        fclose($handle);
			    }
			

			    return array($fields, $results);
		}	




		function savecsvdata(){

			$fieldmap = $this->input->post('fieldmap');	
			$uploadeddacsv = json_decode($this->input->post('uploadedda') , true);
			$area_id = $this->input->post('area_id');

			$insertattendancelog = $this->admin_model->insertattendancelog($fieldmap , $uploadeddacsv , $area_id);	


			echo json_encode($insertattendancelog);

		}
		
		function sendtoemail($exactid = false, $emp_id = false) {
			// deprecated function
			$this->load->model("Attendance_model");
		//	$exactid = 6503;
		//	$emp_id  = 389;
			
			if ($exactid == false && $emp_id	== false) {
				$info 	 = $this->input->post("info");
				
				$exactid = $info['exactid'];
				$emp_id  = $info['empid'];
			}
			
			$ret = $this->Attendance_model->sendleave_email($exactid,$emp_id);
			
			echo json_encode($ret);
			// deprecated function
		}
		
		public function send_email() {
			// called from leave.calendar.js as ajax, function name sendemail
		
			$info = $this->input->post("info");
			
			$grpid 	  = $info['grpid'];
			$empid 	  = $info['empid'];
			$typemode = $info['type_mode'];
			
			if ($typemode == "false") {
				$typemode = false;
			}
		
		//	echo json_encode($grpid."/".$empid."/".$typemode);
		
		/*
			$grpid 	  = "3f3e2b-5a9efe";
			$empid	  = 48;
			$typemode = false;
		*/
			
			if ($grpid == false && $empid == false){
				echo json_encode("false");
			} else {
				$this->load->model("Attendance_model");
				$ret = $this->Attendance_model->send_email($grpid, $empid, $typemode);
				echo json_encode($ret);
			}
			
		}
		
		
		public function phpinfo() {
			phpinfo(); return;
			// echo extension_loaded('openssl')?"Available":"NOT Available";
		}
		
		public function native_sendemail() {
			
		//  589
		//  587 -> use this port
		//  tls 
		
		//	$to      = "alvinjay.merto@minda.gov.ph";
			$to      = "merto.alvinjay@gmail.com";
			$subject = "My subject";
			$txt 	 = "Hello world _ feb 15 2015!";
			$headers = "From: merto.alvinjay@gmail.com" . "\r\n" .
			"CC: alvinjay.merto@minda.gov.ph";
	
			if (mail($to,$subject,$txt,$headers)) {
				echo "emailed";
			} else {
				echo "error";
			}

		}
		
		public function c_igniter() {
			$this->load->library('email');

			$this->email->from('merto.alvinjay@gmail.com', 'alvin merto');
			$this->email->to('alvinjay.merto@minda.gov.ph');
			//$this->email->cc('another@another-example.com');
			//$this->email->bcc('them@their-example.com');

			$this->email->subject('Email Test');
			$this->email->message('Testing the email class.');

			if ( $this->email->send() ) {
				echo "sent";
			} else {
				echo "error";
			}
		}

		public function testemail() {
		
			$mail = new PHPMailer(true);
			
			try {
				//Server settings
				$mail->SMTPDebug  = 2;                                // Enable verbose debug output
			 	$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host       = 'msmail.minda.gov.ph'; 		      // Specify main and backup SMTP servers //msmail.minda.gov.ph //ssl://smtp.gmail.com
				$mail->Port       = 587;                               // TCP port to connect to
				$mail->SMTPAuth   = false;
				$mail->SMTPSecure = "TLS";
			//	$mail->Username   = 'merto.alvinjay@gmail.com';       // SMTP username
			//	$mail->Password   = 'm1797ghtya7vinvyp7l';            // SMTP password
				$mail->Priority   = 1;
			//	$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
			
				
				/*
				$mail->SMTPSecure = 'tls';
				$mail->Host = 'smtp.gmail.com';
				$mail->Port = 587;
				//or more succinctly:
				$mail->Host = 'tls://smtp.gmail.com:587';
				*/
				
				//Recipients
				$mail->setFrom('merto.alvinjay@gmail.com', 'Mailer');
				$mail->addAddress('merto.alvinjay@gmail.com', 'alvin merto minda');     // Add a recipient
				//$mail->addAddress('ellen@example.com');               // Name is optional
				//$mail->addReplyTo('info@example.com', 'Information');
				//$mail->addCC('cc@example.com');
				//$mail->addBCC('bcc@example.com');

				//Attachments
				//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

				//Content
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->Subject = 'Here is the subject';
				$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
				$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
				
				//var_dump($mail); return;
				if ($mail->send()) {
					echo 'Message has been sent';
				}
			} catch (Exception $e) {
				echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
			}
			
		}
		
		function savecheckexact(){
			$checkexact_obj = $this->input->post('checkexact');
			$checkexactlogs_obj = $this->input->post('checkexact_logs');

			// responsible for resetting the 
			$insertcheckexact = $this->admin_model->updatecheckexact($checkexact_obj);
			
			
			if($insertcheckexact){
			
			
				$type_mode = $checkexact_obj['type_mode'];

					if($type_mode == "AMS"){

						$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $checkexactlogs_obj);


						echo json_encode("success AMS");

					}else if ($type_mode == "PS"){

						
						$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $checkexactlogs_obj);
						
						//$this->Globalproc->sendtoemail($details);
						
						echo json_encode($insertcheckexact);

					}else if ($type_mode == "CA"){

						
						$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $checkexactlogs_obj);
						
						//$this->Globalproc->sendtoemail($details);
						
						echo json_encode($insertcheckexact);

					} else if ($type_mode == "CTO"){

						
						$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $checkexactlogs_obj);
						
						
						echo json_encode($insertcheckexact);

					} else if ($type_mode == "OT"){

						$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $checkexactlogs_obj);
						
						
						echo json_encode($insertcheckexact);

					} else if ($type_mode == "PAF"){

						$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $checkexactlogs_obj);


						echo json_encode($insertcheckexact);


					}else if ($type_mode == "OB"){


						foreach ($checkexactlogs_obj as $key => $value) {
							$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $value['data']);
						}


						echo json_encode("success ob");


					}else if ($type_mode == "LEAVE"){

						foreach ($checkexactlogs_obj as $key => $value) {
							$updatecheckexactlogs = $this->admin_model->updatecheckexactlogs($insertcheckexact['exact_id'], $value['data']);
						}

						 $insert_leave_details = $this->admin_model->updatecheckexactleaveslog($insertcheckexact['exact_id'] , $checkexact_obj);
		
						echo json_encode($insertcheckexact);

					}
				
			}

		}
		
		function saveattachmentsonly(){

			$info = $this->input->post('info');
			/*
			$info = [
				"exact_id"    => 11999, // 11999, 
				"attachments" => Array("ab5151fc61272da4ed01a2774a7692f6.tsv")
			];
			*/
			$updateattachments = $this->attendance_model->updateattachmentsonly($info);
			
			echo json_encode($updateattachments);
			//echo json_encode($info);
		}




		function writeactivitylogs(){

				$info = $this->input->post('info');
				$employee_id = $info['employee_id'];
				$exact_id = $info['exact_id'];
				$description = $info['description'];

				$result = $this->admin_model->updateactivitylogs($exact_id , $employee_id , $description);

				if($result){
					echo json_encode($result);
				}
		}


		function checexactapproved(){
			$info = $this->input->post('info');
			
			if ($info['is_approved'] == 2) {
				$this->load->model("v2main/Globalproc");
				
				$del_sql = "delete from checkexact where exact_id = '{$info['exact_id']}'";
				$is_approved = $this->Globalproc->run_sql($del_sql);
				
				if ($is_approved) {
					$del_sql_cea = "delete from checkexact_approvals where exact_id = '{$info['exact_id']}'";
					$is_approved = $this->Globalproc->run_sql($del_sql_cea);
				}
			} else {
				$is_approved = $this->admin_model->is_approvedcheckexact($info['exact_id'] , $info['approve_id'] , $info['is_approved']);
			}
			
			echo json_encode($is_approved);
		}


		function getcheckexactinfo(){

			$info = $this->input->post('info');
	        $result = $this->admin_model->getcheckexactinfo($info['exact_id'] , $info['type']);


	        if($info['type'] == ''){

	        	$filenames = '';	

	 			if($result[0]->attachments != ' '){
	 				$filenames = unserialize($result[0]->attachments);
	 			}		

		        $test = array('serialize' => $filenames);

		        array_push($result , $test);

		    }

	        echo json_encode($result);
 
		}




		function getuserlevel(){
			$info = $this->input->post('info');
			$result = $this->admin_model->getuserlevel($info['level']);
	        echo json_encode($result);
		}


		function getleaves(){
			$info = $this->input->post('info');
			$result = $this->admin_model->getleaves();
			 echo json_encode($result);
		}


		function getemployees(){

				$DB2 = $this->load->database('sqlserver', TRUE);
				$query =  $DB2->query("SELECT * FROM employees ORDER BY f_name ASC"); 
				return $query->result();
		}




		public function attrecord(){
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
			
			//$this->load->view('hrmis/attendance/attendance_record_view');
			 $this->load->view('hrmis/admin_view',$data);

		}



		public function attrecord_1(){


			//$data['latest_update'] = $this->attendance_model->get_attemndance_logs_last_update($area_id = 1);

			$data['main_content'] = 'admin/reports/attendancer_record_view-back';
			$this->load->view('admin/admin_view',$data);

		}



		public function employeeschedule(){


			$getallemployeeshift = $this->attendance_model->getallemployeeshift();
	
			$data['employees'] = $getallemployeeshift;
			$data['title'] = '| Employee Schedule';
			$data['shifts'] =  $this->attendance_model->getshifts();

			$data['main_content'] = 'hrmis/attendance/timetable_view';
			$this->load->view('hrmis/admin_view',$data);

		}


		public function updateemployeeshift(){

			$info = $this->input->post('info');

			$result  =  $this->attendance_model->updateemployeeshift($info);

			echo json_encode($result);
		}




		public function shiftmanagement(){


			$data['shifts'] =  $this->attendance_model->getshifts();
			$data['title'] = '| Shift Management';
			$data['main_content'] = 'hrmis/attendance/shift_schedule_view';
			$this->load->view('hrmis/admin_view',$data);
		}



		function getshiftslogs(){

			$info = $this->input->post('info');
			$result  =  $this->attendance_model->getshiftslogs($info['shift_id']);

			echo json_encode($result);

		}



		function updateshifts(){

			$info = $this->input->post('info');
			$result  =  $this->attendance_model->update_shifts($info);

			echo json_encode($result);

		}


		function test(){
			try{
				
				$client = new SoapClient("http://office.minda.gov.ph:9002/Service/MinDAFSVC.svc?wsdl");
				// $client = new SoapClient("https://office.minda.gov.ph:9003/Testemail/");
				// Set parameters
				//$parms['LODI']['MemberAge'] = 'hello there';

				// Call web service PassMember methordd
				$webService = $client->testtest();
				//$wsResult = $webService->PassMemberResult();
				 
				// print response
				print_r($webService);
					   
			} catch (Exception $e) {
					   echo 'Caught exception:',  $e->getMessage(), "\n";
			}  
		}
		
		function testapk() {
			    include(APPPATH."libraries/zklib/zklib.php");
   			    $zk = new ZKLib("192.168.1.13", 4370);


   			    $ret = $zk->connect();
				
				
		}
		
		function testtime() {
			$time = "13:98";
			list($h, $m) = explode(':', $time);
			$seconds = ($h * 3600) + ($m * 60);
	
			$time2 = "10:50";
			list($h2, $m2) = explode(':', $time2);
			$seconds2 = ($h2 * 3600) + ($m2 * 60);
			
			$total = $seconds + $seconds2;
			echo $total;
			echo "<br/>";
			
			$dtF     = new \DateTime('@0');
			$dtT 	 = new \DateTime("@$total");
			echo $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');	
		}
		
		function showjo() {
			$sql = "select employee_id, f_name from employees where employment_type='JO'";
			
			$this->load->model("v2main/Globalproc");
			$d   = $this->Globalproc->__getdata($sql);
		
			$delete = "delete from employee_schedule where ";
			$count  = 0;
			foreach($d as $dd) {
				//echo $dd->employee_id." &nbsp;&nbsp;&nbsp; ".$dd->f_name."<br/>";
				$delete .= "employee_id = '{$dd->employee_id}'";
				$delete .= ($count < count($d)-1)?" or":null;
				$delete .= "<br/>";
				$count++;
			}
			echo $delete;
		}
		
		function testapi() {
			// echo (int)$_GET["a"] + (int)$_GET["b"];
			// echo json_encode("hello");
			$c = $_POST['a'];
			// $b = $_GET['b'];
			
			// $c = json_decode( json_encode($a) );
			// $c = json_decode( $a );
			//echo $a;
		//	$c = json_encode( $a );
			
		//	$d = json_decode( $a );
			
			// var_dump( $a );
			
			$ex = explode("|",$c);
			
			$DB2   = $this->load->database('sqlserver', TRUE);
			
			$samp = null;
			$sql  = "";
			
			$ar_code = null;
			foreach($ex as $str) {
				if (strlen($str) > 1) {
				//	$str = trim("\"", $str);
				//	$str = trim("'", $str);
				//	$str = trim("{", $str);
				//	$str = trim("}", $str);
					
					$vals   = explode(",",$str);
					
					$sql .= "insert into checkinout (biometric_id,area_id,checktime,checktype) values(";
					$count = 0;
					foreach ($vals as $vs) {
						$perval = explode("=",$vs);
						// PIN			= biometric id 
						// ctime		= checktime
						// ctype 		= statustype 
						// macali 		= machine alias id
											// davao_new 
											// cagayan
											// butuan
											// koronadal
											// zamboanga
					
						$theval = trim($perval[1],"'");
						$theval = trim($perval[1],"\}");
						
						switch( $perval[0] ) {
							case "ctime":
								$theval = "'".date("n/j/Y g:i A", strtotime( trim($perval[1],"'") ))."'";
								break;
							// case "'macali'":
							case "macali":
								if ($ar_code == null) {
									$newv   = trim($perval[1],"'");
									$arcode = substr( $newv,0,3);
									$getar  = "select area_id from areas where area_code = '{$arcode}'";
									
									$ar 	= $DB2->query($getar);
									$ar_res = $ar->result();
								
									$theval = $ar_code = "'".$ar_res[0]->area_id."'";
								} else {
									$theval = $ar_code;
								}
								break;
						}		
						
						$sql .= "{$theval}";
						
						if ($count != count($vals)-1) {
							$sql .= ",";
						}

						$count++;
					}
					$sql .= ");";
				}
			}
			echo $sql;
		//	echo $arcode;
		//	$flag = $DB2->query($sql);
			
			echo $flag;
		//	echo $sql;
			
			
			// var_dump($ex);
		//	echo $a." and ".$b . " from php"; 
			// echo "hello world";
			/*
			if (isset($_SERVER['HTTP_ORIGIN']) === true) {
				$origin = $_SERVER['HTTP_ORIGIN'];
				
				$allowed_origin = array("http://localhost/minda_design/index.html");
					
				if (in_array($origin,$allowed_origin,true)===true) {
					header("Access-Control-Allow-Origin: ". $origin);
					header("Access-Control-Allow-Credentials: true");
					header("Access-Control-Allow-Methods: POST");
					header('Access-Control-Allow-Headers: Content-Type');
				}
			}
			*/
		}
		
		function verifyuser() {
			$username = $_GET['username'];
			$password = md5($_GET['password']);
			
			$DB2   = $this->load->database('sqlserver', TRUE);
			$query =  $DB2->query("SELECT * FROM users where Username = '{$username}' and Password = '{$password}'"); 
			$q     =  $query->result();
			
			if (count($q)==0) {
				echo "false";
			} else {
				echo "true";
			}
			
		}
		
		function testbio() {
			include(APPPATH."libraries/zklibrary.php");
			$zk = new ZKLibrary('192.168.1.202', 4370);
			
			echo 'Requesting for connection</br>';
			$zk->connect();
			/*
			echo 'Connected</br>';
			$zk->disableDevice();
			echo 'disabling device</br>';
			$users = $zk->getUser();
			
			var_dump($users);
			*/
		}
	}