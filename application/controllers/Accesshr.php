<?php 
	
	class Accesshr extends CI_Controller {
		
		public function gettime($from = '', $to = '') {
			// $this->load->model("v2main/Globalproc");
			
			header("Access-Control-Allow-Origin: *");
			
			$this->load->database("sqlserver", TRUE);
			
			$to   = date("Y-m-d",strtotime($to." +1 days"));
			$from = date("Y-m-d", strtotime($from));
			$sql  = "select * from checkinout where cast(checktime as datetime) between '{$from}' and '{$to}'";
				
			@$ret = $this->db->query($sql);
			
			$this->db->close();
			echo json_encode($ret->result());
			
		}
		
		public function syncemps() {
			header("Access-Control-Allow-Origin: *");
			
			$this->load->database("sqlserver", TRUE);
			
			$sql  = "select biometric_id, firstname, m_name, l_name, department_id, position_id, daily_rate, employment_type from employees where status = '1'";
				
			@$ret = $this->db->query($sql);
			
			$this->db->close();
			echo json_encode($ret->result());
		}
	}

