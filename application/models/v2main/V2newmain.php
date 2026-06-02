<?php
		
	class V2newmain extends CI_Model {
		public function __construct() {
			//parent::__construct();
			// $this->load->model("v2main/Globalproc");
		}

		public function pingemail($email) {
			
			// verify if its a minda email domain

			$details = array(
				"to" => $email,
				"subject" => "Welcome to MinDA",
				"msg"     => "Greetings from Minda",
				"headers" => "From: Minda"
			);
			
			return mail($details['to'],
						$details['subject'],
						$details['msg'],
						$details['headers']);
		}

		public function sendcredentials($det) {
			
			$msg = "
				<p> Username: {$det['uname']}</p>
				<p> Password: {$det['password']}</p>
			";

			$details = array(
				"to" 	  => $det['email'],
				"subject" => "Your Account Login",
				"msg"     => $msg,
				"headers" => "From: KMD-Minda"
			);
			
			return mail($details['to'],
						$details['subject'],
						$details['msg'],
						$details['headers']);
		}

		public function savecredentials($data) {

		}
		
		public function checkfornotimepassslip($date_) {
			$this->load->model("v2main/Globalproc");
		
			$date_ = date("n/j/Y",strtotime($date_));
			$sql   = "select 
						ce.time_out, 
						ce.time_in, 
						ce.exact_id,
						ce.checkdate,
						e.employee_id,
						e.email_2 
						from checkexact as ce
							JOIN employees as e 
								on ce.employee_id = e.employee_id
						where ce.type_mode = 'PS' and ce.checkdate = '{$date_}' 
							and ce.time_in is NULL 
							and ce.time_out is NOT NULL";

			return $this->Globalproc->__getdata($sql);

		}

	}

