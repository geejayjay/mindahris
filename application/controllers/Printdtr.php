<?php 
	
	class Printdtr extends CI_Controller {
		public function now() {
			$this->session->set_userdata('database_default', 'sqlserver');
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("v2main/Timerecords");
			
			$calendar = $_GET['dates'];
			$hv 	  = $_GET['id'];
			
			$from 	  = explode("_",$calendar)[0];
			$to 	  = explode("_",$calendar)[1];
			
			$bu		  = base_url();
			
			$new_cal_a = date("n/j/Y",strtotime($from));
			$new_cal_b = date("n/j/Y",strtotime($to));
	
			echo "<script>";
				echo "var BASE_URL 		 = '{$bu}';";
				echo "var calendar 		 = '{$new_cal_a}-{$new_cal_b}';";
				echo "var hv 			 = '{$hv}';";
			echo "</script>";
	
			$this->Timerecords->setfrom( date("n/j/Y",strtotime($from) ) );
			$this->Timerecords->setto( date("n/j/Y",strtotime($to) ) );
		
			$dview 	  = "";
		
			$this->Timerecords->setemp($hv);
				
				$b_sql = "select 
							e.biometric_id, 
							e.f_name,
							e.email_2,
							p.position_name,
							e.employment_type,
							s.shift_name
						  from employees as e 
							JOIN positions as p 
								on e.position_id = p.position_id
							LEFT JOIN employee_schedule as es 
								on e.employee_id = es.employee_id
							LEFT JOIN shift_mgt as s 
								on es.shift_id = s.shift_id
							where e.employee_id = '{$hv}'";
							
				$bio   = $this->Globalproc->__getdata($b_sql);
				
				if (count($bio)==0){ return; }
				
				$this->Timerecords->setbio( $bio[0]->biometric_id );
					
				$dtr = $this->Timerecords->gettime();
				
				// loop from here
					$data['under'] = $this->Timerecords->return_("total_unders");
					$data['late']  = $this->Timerecords->return_("total_lates");
					$data['daysp'] = $this->Timerecords->return_("total_daysp");
					$data['hourp'] = $this->Timerecords->return_("total_hourp");
					
					$data['dtr']      = $dtr;
					$data['coverage'] = date("F d, Y", strtotime($from))."-".date("F d, Y",strtotime($to));
					$data['name']     = $bio[0]->f_name;
					$data['pos']	  = $bio[0]->position_name;
					$data['empid']	  = $hv;
					$data['emptype']  = strtolower($bio[0]->employment_type);
					$data['sked']	  = $bio[0]->shift_name;
					$dview		 	 .= $this->load->view('v2views/Timetablepdf',$data,true);
				echo $dview;			
		}
	}

