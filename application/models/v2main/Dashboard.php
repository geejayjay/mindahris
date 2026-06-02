<?php 
	
	class Dashboard extends CI_Model {
		
		function getfrom($arcode) {
				include(APPPATH."libraries/zklib/zklib.php");
				$this->load->model("admin_model");
				$this->load->model("v2main/Globalproc");
				
				// get area_code from url
				$area_code 	  = $arcode;
				// $area_code 	  = $this->uri->segment(3);
				
				// get area id base from area_code 
				$area	 	  = $this->Globalproc->get_details_from_table("areas",["area_code"=>$area_code],["area_id","ipaddress"]);
	
				// set area_id and area_ip_address
				$area_id      = $area['area_id'];
				$ip   		  = $area['ipaddress'];
				
				// get last_update base from the area_id
				$last_update  = $this->attendance_model->get_attemndance_logs_last_update($area_id); // mark 2
				$last_update  = $last_update[0]->checktime;
				
				// get all employees
				$getemployees = $this->admin_model->getemployees();
				
				$zk   		  = new ZKLib("{$ip}", 4370);
			
				// var_dump($zk); return;
				// echo $ip;
				$pass 		  = null;
				$data 		  = null;
				
				$ret 		  = $zk->connect();
				
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
								
				               // if(strtotime($attendancedata[3]) > strtotime($last_update)){
									
				                	$checktime =  date( "n/j/Y", strtotime( $attendancedata[3] ) ).' '.date( "g:i A", strtotime( $attendancedata[3] ) );
										
										foreach ($getemployees as $rr) {
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
										
				               // }
								
				          }
						  
				  
				        $zk->enableDevice();
				        sleep(1);
				        $zk->disconnect();
						
						$pass = array('data' => $data , 'device_info' => $device_info);
	
					} else {
						$pass = false;
					}
					var_dump($pass); return;
				return $pass;
				
		}
		
		function keepabackup($filename = false,$filecontent = false) {
			$this->load->helper("file");
			
			//$filename 	 = "hello.txt";
			//$filecontent = "hello world from method";
			
			if (write_file(FCPATH."attendancebackup/".$filename, $filecontent) == FALSE) {
				return false;
			} else {
				return true;
			}
		}
		
		function save_sync_data_log($thedata, $arcode){
				$this->load->model("v2main/Globalproc");
				$this->load->model("admin_model");
				$this->load->model("attendance_model");
				
				// $info   = $this->input->post('info_a');
				// $data_1 = json_decode($info);
				
				// $data   = (array) $data_1->data;
				$data 	   = $thedata;
				
				// get area code from the url
				$area_code = $arcode;
			
				$area_id   = $this->Globalproc->get_details_from_table("areas",["area_code"=>$area_code],["area_id","ipaddress"]);
				$area_id   = $area_id['area_id'];
				
				$fieldmap  = array();

				$fieldmap[0]['checkinout'] = "biometric_id";
				$fieldmap[0]['csv'] 	   = "biometric_id";
				$fieldmap[1]['checkinout'] = "checktime";
				$fieldmap[1]['csv']		   = "checktime";
				$fieldmap[2]['checkinout'] = "checktype";
				$fieldmap[2]['csv'] 	   = "status";
				
				$insertattendancelog = $this->admin_model->insertattendancelogv2($fieldmap , $data , $area_id);	
				
				if($insertattendancelog){
					$latest_update = $this->attendance_model->get_attemndance_logs_last_update($area_id);
					return $latest_update[0]->last_update;
				}
			return false;	
		}
		
	}

