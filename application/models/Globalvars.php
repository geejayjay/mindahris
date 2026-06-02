<?php 
	
	class Globalvars extends CI_Model {
		public $employeeid;
		public $usertype;
		public $emptype;

		public function __construct() {
			$this->employeeid = $this->session->userdata('employee_id');
			$this->usertype   = $this->session->userdata('usertype');
			$this->emptype	  = $this->session->userdata('employment_type');	
		}
	}
