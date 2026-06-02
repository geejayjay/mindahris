<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class Monitoring extends CI_Controller{
		

		public function __construct(){
			parent::__construct();
			$this->load->model('admin_model');
			$this->load->model('attendance_model');
			$this->load->model('reports_model');
			$this->load->model('personnel_model');
			$this->load->model('leave_model');

		}


		function dashboard(){

			if($this->session->userdata('position_name') == 'Security' || $this->session->userdata('usertype') == 'admin'){

				$guard_employee_id = $this->session->userdata('employee_id');
				$guard_area_id = $this->session->userdata('area_id');

				$data['title'] 		  	        = '| AMS/PS Monitoring';
				$data['headscripts']['js'][]    = base_url()."v2includes/js/getps.js";
				$data['headscripts']['style'][] = base_url()."v2includes/style/psstyle.css";
 				$data['main_content'] 	        = 'hrmis/monitoring/monitoring_dashboard_view';
				$data['guard_employee_id']      = $guard_employee_id;
				$data['guard_area_id'] 	        = $guard_area_id;
				$this->load->view('hrmis/admin_view',$data);
			 
			}else{
				 redirect('/accounts/login/', 'refresh');
			}
		}


		function getapprovedps() {

			$getApprovedPS = $this->reports_model->get_approved_ps_applications();

			echo json_encode($getApprovedPS);
	
		}
		
		function getpstime() {
			$dets = $this->input->post("info");
			$ei   = $dets['exactid'];
			
			$this->load->model("v2main/Globalproc");
			$ps = $this->Globalproc->gdtf("checkexact",['exact_id'=>$ei],["time_in","time_out","remarks"]);
			
			echo json_encode($ps);
		}
		
		function updatepstime() {
			
			$vals  = $this->input->post("info");
			$exact = $vals['exactid'];
			$vals  = $vals['val'];
			
			$details = [];
			/*
				"time_in"  => $vals['t_in'],
				"time_out" => $vals['t_out'],
				"remarks"  => $vals['remtext']
			*/
			
			if ($vals['t_in'] != null) {
				$details['time_in'] = $vals['t_in'];
			}
			
			if ($vals['t_out'] != null) {
				$details['time_out'] = $vals['t_out'];
			}
			
			if ($vals['remtext'] != null || !empty($vals['remtext'])) {
				$details['remarks'] = $vals['remtext'];
			}
			
			$this->load->model("v2main/Globalproc");
			
			/*
			$details['time_in']  = "5:00 PM";
			$details['time_out'] = "2:00 PM";
			$details['remarks']  = "remarks here";
			$exact				 = "11708";
			*/
			
			$ret = $this->Globalproc->__update("checkexact",$details,['exact_id'=>$exact]);
			
			$times = $this->Globalproc->gdtf("checkexact",['exact_id'=>$exact],["time_in","time_out","employee_id","ps_type","checkdate","grp_id"]);
			
			if (strlen($times[0]->time_in) > 1 && strlen($times[0]->time_out) > 1) {	
					$start_time  = new DateTime($times[0]->time_out);
					$end_time    = new DateTime($times[0]->time_in);
					$interval    = $start_time->diff($end_time);
					$cto_hours 	 = $interval->format('%h');
					$cto_mins    = $interval->format('%i');
					
					$type_details = [
						"typemode"			=> "ps",
						"leave_value"		=> $times[0]->ps_type,
						"date_inclusion"	=> date("M d, Y", strtotime($times[0]->checkdate)),
						"hrs"				=> $cto_hours,
						"mins"				=> $cto_mins
					];
					
					$ret = $this->Globalproc->calc_leavecredits($times[0]->employee_id, $times[0]->grp_id, $type_details);
				
			}
			
			echo json_encode( $ret );
			
		}

		function getemployeeams(){

			$info = $this->input->post('info');	
			$getemployeeams = $this->reports_model->get_employee_ams($info);

			echo json_encode($getemployeeams);		
		}



		function updatetimeps(){

			$info = $this->input->post('info');	
			$result = $this->reports_model->update_time_ps($info);

			if($result){
				echo json_encode($result);
			}

		}

		function updatetimeams(){

			$info = $this->input->post('info');	
			$result = $this->reports_model->update_time_ams($info);

			if($result){
				echo json_encode($result);
			}

		}

		function getamsemployee(){
			$info = $this->input->post('info');	
			$result = $this->reports_model->get_ams_employee($info['date'], $info['employee_id']);
			if($result){
				echo json_encode($result);
			}else{
				echo json_encode(false);
			}

		}
		
		function names() {
			$this->load->model("v2main/Globalproc");
			
			// $sql = "select employee_id, id_number, f_name from employees where status = 1";
			
			$names = $this->Globalproc->gdtf("employees",["status" => 1],["employee_id","id_number","f_name"]);
			
			echo "<table>";
				echo "<thead>";
					echo "<th>";
						echo "Employee ID";
					echo "</th>";
					echo "<th>";
						echo "ID Number";
					echo "</th>";
					echo "<th>";
						echo "Full Name";
					echo "</th>";
				echo "</thead>";
				foreach($names as $n) {
					echo "<tr>";
						echo "<td>";
							echo $n->employee_id;
						echo "</td>";
						echo "<td>";
							echo $n->id_number;
						echo "</td>";
						echo "<td>";
							echo $n->f_name;
						echo "</td>";
					echo "</tr>";
				}
			echo "</table>";
		}

	}

