<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class Personnel extends CI_Controller{
		


		public function __construct(){
			parent::__construct();
			$this->load->model('admin_model');
			$this->load->model('personnel_model');
			

		}

		public function index(){
			$data['main_content'] = 'admin/personnel/personnel_view';
			$this->load->view('admin/admin_view',$data);

		}


		/* navigations */

		public function areas(){
			$data['main_content'] = 'hrmis/personnel/area_view';
			$getareas = $this->admin_model->getareas();

			$data['title'] = '| Area Setup';
			$data['areas'] = $getareas;
			$this->load->view('hrmis/admin_view',$data);
		}




		public function officedivision(){

			$this->load->model('personnel_model');

			$data['sub_pap_division_tree'] = $this->personnel_model->getsubpap_divisions_tree();

			$data['title'] = '| Division Setup';
			$data['main_content'] = 'hrmis/personnel/department_view';
			$this->load->view('hrmis/admin_view',$data);
		}




		public function position(){

			$this->load->model('personnel_model');

			$data['title'] = '| Positions';
			$data['main_content'] = 'hrmis/personnel/position_view';
			$data['get_positions'] = $this->personnel_model->getpositions('NULL');
			$this->load->view('hrmis/admin_view',$data);

		}



		public function profile($company_id = ''){
			$this->load->model("v2main/Globalproc");
			$this->load->model("Globalvars");
			$data['admin'] = ($this->Globalvars->usertype != "user")?true:false;
			
			$employee_id = $this->session->userdata('employee_id');
			$division_id = $this->session->userdata('division_id');
			$dbm_sub_pap_id = $this->session->userdata('dbm_sub_pap_id');

			$data['title'] = "| Profile";
			
			
			if($company_id == '' || $employee_id == $company_id){

				$data['employee_id'] = $employee_id;
				$info['employee_id'] = $employee_id;
				
				$data['employees'] = $this->personnel_model->get_employees($info);
				$info['division_id'] = $division_id;
				$info['dbm_sub_pap_id'] = $dbm_sub_pap_id;
				$data['team_members'] = $this->personnel_model->get_team_members($info);

			}else{

				$info['employee_id'] = $company_id;
				$data['employees'] = $this->personnel_model->get_employees($info);
				$info['division_id'] = $data['employees'][0]->Division_id;
				$info['dbm_sub_pap_id'] = $data['employees'][0]->DBM_Pap_id;
				$data['team_members'] = $this->personnel_model->get_team_members($info);
				$data['employee_id'] = $company_id;

			}
		
			$data['main_content'] = 'hrmis/personnel/profile_view';
			$this->load->view('hrmis/admin_view',$data);
		
			
		}







		public function employee($url = "" ){


			if($this->session->userdata('is_logged_in')!=TRUE){
				  redirect('/accounts/login/', 'refresh');
			}else{

			$getemployees = $this->admin_model->getemployees();
			$getareas = $this->admin_model->getareas();

			 	$data['title'] = '| Employees';


			if($url == ''){

				$data['employees'] = $getemployees;
				$data['areas'] = $getareas;
				$data['sub_pap_division_tree'] = $this->personnel_model->getsubpap_divisions_tree();
				$data['positions'] = $this->personnel_model->getpositions('NULL');
				$data['main_content'] = 'hrmis/personnel/employee_view';

			}else if ($url == 'import'){

				$data['areas'] = $getareas;

				$data['page_type'] = 'Edit';
				$data['main_content'] = 'hrmis/personnel/import_employee_view';	

			}


				$this->load->view('hrmis/admin_view',$data);
			
			}

		}	



		/* functions */	



	function get_employee_id(){

		$info = $this->input->post('info');	
		 $result =  $this->personnel_model->getemployeeid($info['biometric_id'] , $info['area_id']);

		 echo json_encode($result[0]->employee_id);

	}

	function getsubpap_divisions_tree(){


		$data['sub_pap_division_tree'] = $this->personnel_model->getsubpap_divisions_tree();

	}




	function updatepositions(){
		
			$info = $this->input->post('info');	
			$update_position = $this->personnel_model->update_position($info);

			echo json_encode($update_position);
	}


	function uploademployees(){
		    
	        $status = 'error';
	        $msg = 'No file uploaded or file upload error.';
	        $csv = array(null, null);

	        $config['allowed_types'] = 'csv';
	        $config['upload_path'] = FCPATH . 'assets/import/';
	        $config['max_size']	= '900000';
	        $config['file_name'] = 'emp_upload' . time();

	        if (!is_dir($config['upload_path'])) {
	            mkdir($config['upload_path'], 0777, true);
	        }

	        $this->load->library('upload', $config);

	        if (empty($_FILES)) {
	            $msg = 'No file was submitted for upload.';
	            log_message('error', '[UPLOAD EMPLOYEES] Upload failed: ' . $msg);
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
	            log_message('error', '[UPLOAD EMPLOYEES] Upload failed: ' . $msg);
	            echo json_encode(array('status' => 'error', 'message' => $msg));
	            return;
	        }

	        if (empty($csv[0]) || empty($csv[1])) {
	            $msg = 'The uploaded CSV file is empty or has invalid formatting.';
	            log_message('error', '[UPLOAD EMPLOYEES] CSV parse failed: ' . $msg);
	            echo json_encode(array('status' => 'error', 'message' => $msg));
	            return;
	        }

	      $getemployeefields = $this->admin_model->getemployeefields();

	      foreach($getemployeefields as $row){
	      		$columns[] = $row->columns;
	      }

	      echo json_encode(array('csvfields'=> $csv[0], 'data' => $csv[1] , 'employeefields' => $getemployeefields));

	      						/* csv fields            csv uploaded data         checkinout fields from db */
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
			                    $results[$col][$fields[$k]] = htmlentities($value, ENT_QUOTES, 'UTF-8');
			                }
			            }
			            $col++;
			            unset($row);
			        }
			        if (!feof($handle)) 
			        {
			            log_message('error', '[UPLOAD EMPLOYEES] CSV Import: unexpected fgetcsv() fail on file ' . $filename);
			        }
			        fclose($handle);
			    }
			
			    return array($fields, $results);
		}	



		function savecsvdata(){

			$fieldmap = $this->input->post('fieldmap');	
			$uploadeddacsv = json_decode($this->input->post('uploadedda') , true);
			$area_id = $this->input->post('area_id');


			$insertemployees = $this->admin_model->insertemployees($fieldmap , $uploadeddacsv , $area_id);	


			echo json_encode($insertemployees);

		}


		function updateemployees(){

			$info = $this->input->post('info');	

			$update = $this->personnel_model->update_employees($info);

			if($update){
				echo json_encode(true);
			}else{
				echo json_encode(false);
			}

		}



		function getemployees(){

			$info = $this->input->post('info');	

			$result = $this->personnel_model->get_employees($info);

			if($result){
				echo json_encode($result);
			}else{
				echo json_encode(false);
			}

		}



		function uploadattachment(){

			$checkFilenames = [];

			$success = 0;

				for($i=0; $i<count($_FILES['file']['name']); $i++){
				    $dir_path = FCPATH . "assets/profiles/";
				    if (!is_dir($dir_path)) {
				        mkdir($dir_path, 0777, true);
				    }
				    $ext = explode('.', basename( $_FILES['file']['name'][$i]));
				    $new_File_name = md5(uniqid()) . "." . $ext[count($ext)-1]; 
				    $target_path = $dir_path . $new_File_name;

				    if(move_uploaded_file($_FILES['file']['tmp_name'][$i], $target_path)) {

 							echo json_encode(array('filename' => $new_File_name));	
				       
				    } else{
				        log_message('error', '[UPLOAD PROFILE] Failed to move uploaded profile picture to: ' . $target_path);
				    }
				}
				
		}



		function uploaesingature(){

			$checkFilenames = [];

			$success = 0;

				for($i=0; $i<count($_FILES['file']['name']); $i++){
				    $dir_path = FCPATH . "assets/esignatures/";
				    if (!is_dir($dir_path)) {
				        mkdir($dir_path, 0777, true);
				    }
				    $ext = explode('.', basename( $_FILES['file']['name'][$i]));
				    $new_File_name = md5(uniqid()) . "." . $ext[count($ext)-1]; 
				    $target_path = $dir_path . $new_File_name;

				    if(move_uploaded_file($_FILES['file']['tmp_name'][$i], $target_path)) {

 							echo json_encode(array('filename' => $new_File_name));	
				       
				    } else{
				        log_message('error', '[UPLOAD ESIGNATURE] Failed to move uploaded signature to: ' . $target_path);
				    }
				}


		}


		function saveprofile(){

			$info = $this->input->post('info');
			$updateattachments = $this->personnel_model->update_profile_picture($info);

			echo json_encode($updateattachments);
		}


		function saveesignature(){
			$info = $this->input->post('info');
			$updateattachments = $this->personnel_model->update_e_signature($info);

			echo json_encode($updateattachments);
		}


		function update_password(){

			$info = $this->input->post('info');
			$update_password = $this->personnel_model->update_password($info);


			echo json_encode($update_password);
	

		}

		function updateprofile(){
			$info = $this->input->post('info');
			$update_profile = $this->personnel_model->update_profile($info);

			echo json_encode($update_profile);
		}

		function add_new_position() {
			$this->load->model("v2main/Globalproc");
			
			$dets 		   = $this->input->post("info");
			$position_name = $dets['position_name'];
			
			$ret = $this->Globalproc->__save("positions",["position_name"=>$position_name,"status"=>1]);
			
			if ($ret){
				$ret = $this->Globalproc->getrecentsavedrecord("positions", "last_id");
				echo json_encode($ret[0]->last_id);
			}
		}

		function testtest() {
			echo phpinfo();
		}

	}