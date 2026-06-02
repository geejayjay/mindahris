<?php 
	
	class Testemail extends CI_controller{ 
		
		public function send() {
			$config = Array(
				'protocol' => 'smtp',
				'smtp_host' => 'ssl://smtp.googlemail.com',
				'smtp_port' => 465,
				'smtp_user' => 'minda.smtpsender@gmail.com',
				'smtp_pass' => 'ghty56rueiwoqp',
				'mailtype'  => 'html', 
				'charset'   => 'iso-8859-1'
			);
			$this->load->library('email', $config);
			$this->email->set_newline("\r\n");
			
			$this->email->from('merto.alvinjay@gmail.com', 'From Alvin Gmail');
			$this->email->to('alvinjay.merto@minda.gov.ph');

			$this->email->subject('Email Test');
			$html = "<html>
						<body>
							hello
						</body>
					</html>
					";
			$this->email->message($html);

			if ($this->email->send()) {
				echo "mail sent";
			}
		}
		
		public function testdb() {
			$DB2   = $this->load->database('sqlserver', TRUE);
			
			$query = "Select * from users where user_id = '50'";
			
            $query = $DB2->query($query);
			$result = $query->result();
			
			echo $result[0]->Username;
			echo $result[0]->Password;
			echo "<br/>";
			echo count($result);
		}
		
		public function ledger($tbl = '', $emp_id = '') {
			$data['title'] = '| My Leave Ledger';

			echo "<script>";
				echo "var isadmin = false;";
			echo "</script>";
			
			echo "<style>";
				echo ".leave_ledger_btn {
							background:#96d0f1;
							color: #333 !important;
						}";
			echo "</style>";
			
			if ($tbl == '') {
				$data['dont_display'] = true;
				$data['headscripts']['style'][0]  = base_url()."v2includes/style/hr_dashboard.style.css";	
				$data['headscripts']['style'][1]  = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
				
				$data['headscripts']['style'][2]  = base_url()."v2includes/style/leavemgt.style.css";
				
				$data['headscripts']['js'][0] 	  = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js";				
				$data['headscripts']['js'][1]     = base_url()."v2includes/js/windowresize.js";				
				$data['headscripts']['js'][2]     = base_url()."v2includes/js/leavemgt.procs.js";
				$data['headscripts']['js'][3] 	  = base_url()."v2includes/js/my_leave_ledger.js";
				
				$data['main_content'] 			  = "v2views/leavemgt";
			} elseif ($tbl == "coc") {
				$this->load->model("v2main/Globalproc");
				$this->load->model("Globalvars");
				
				if ($emp_id == '' || $this->session->userdata("usertype") != "admin") {
					$emp_id = $this->Globalvars->employeeid;
				}
				
				echo "<script>";
					echo "var t_emp_id = '{$emp_id}'";
				echo "</script>";
				
				$data['emp_name'] 				= $this->Globalproc->gdtf("employees",["employee_id"=>$emp_id],["f_name"])[0]->f_name; 
				
				$data['headscripts']['style'][] = base_url()."v2includes/style/ctoot.style.css";
				/*$data['headscripts']['js'][] 	= base_url()."v2includes/js/ctoot.js"; */
				
				$sql   = "select eot.*,e.f_name from employee_ot_credits as eot
					  JOIN employees as e on eot.emp_id = e.employee_id
					  where eot.emp_id = '{$emp_id}' ORDER BY eot.elc_otcto_id ASC";
				$data['data']  = $this->Globalproc->__getdata($sql);
				
				$data['main_content'] 			= "v2views/ctoledger_new";
			}
			
			$this->load->view('hrmis/admin_view',$data);
		}
		
		function test_a() {
			// 448980
			
			$seconds  = 448980;
			$dtF      = new \DateTime('@0');
			$dtT 	  = new \DateTime("@$seconds");
			$a		  = $dtF->diff($dtT)->format('%a:%h:%i');	
					
			$days     = $dtF->diff($dtT)->format('%a');	
			$hour	  = $dtF->diff($dtT)->format('%h');	
			$mins	  = $dtF->diff($dtT)->format('%i');	
			
			echo $days."<br/>";
			echo $hour."<br/>";
			echo $mins."<br/>";
			echo $a."<br/>";
			
			echo "seconds of days: <br/>";
			echo (24*$days)/8; // convert days to 8 hour format 
			
			// add the remaining hours in hours if more than 7
		}
		
		function sendtext() {	
			$to = "639097434684@gmail.com";
			$from = "test";
			$msg 	= "hello this is a text message";

			var_dump(mail($to,"",$msg));
		}
		
		function phpver() {
			echo phpinfo();
		}
		
		public function testtest() {
			echo "well hello";
		}
	}
