<?php 
	class Formpopulate extends CI_Model {
		public function __construct() {
			$this->load->model("Globalvars");
		}

		public function __getempdata() {
			$this->load->model("v2main/Globalproc");

			$empid = $this->Globalvars->employeeid;
			/*
			$sql   = "select * from employees 
						JOIN DBM_Sub_Pap on 
						employees.DBM_Pap_id = DBM_Sub_Pap.DBM_Sub_Pap_id
						where employees.employee_id = '{$empid}'";
			*/
			$sql   = "select * from employees as e
					  JOIN Division as d
					  	on e.Division_id = d.Division_Id
					  where e.employee_id = '{$empid}'";					

			return $this->Globalproc->__getdata($sql);

		}
	}
