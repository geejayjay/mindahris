<?php 
	
	class Senddtrmodel extends CI_Model {
=
		public function senddtr() {
			$this->load->model("v2main/Timerecords");
			$this->load->model("v2main/Globalproc");
				
		//	$calendar = $this->input->post("calendar_");
		//	$hrview	  = $this->input->post("hrviewdets"); 
		//	$hrview   = $this->selected;
		//	$calendar = $this->calendar;
			
			/*
			$from 	  = explode("-",$this->calendar)[0];
			$to 	  = explode("-",$this->calendar)[1];
			
			
			// 2016-07-08
			$this->Timerecords->setfrom( $from );
			$this->Timerecords->setto( $to );
			
			$dview 	  = "";
			
			// echo $this->selected['selected'][0];
			
			if (!isset($this->selected['selected'][$this->row])) { return true; }

				$this->Timerecords->setemp($this->selected['selected'][$this->row]);
				
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
							where e.employee_id = '{$this->selected['selected'][$this->row]}'";
				$bio   = $this->Globalproc->__getdata($b_sql);
				
				if (count($bio)==0){ break; }
				
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
					$data['empid']	  = $this->selected['selected'][$this->row];
					$data['emptype']  = strtolower($bio[0]->employment_type);
					$data['sked']	  = $bio[0]->shift_name;
					$dview		 	 .= $this->load->view('v2views/timetableprint',$data,true);
					
					// send email
						$details['subject'] = 'DTR';
						$details['from'] 	= 'Minda - HR';
						$details['to'] 		= $bio[0]->email_2;
						$details['message'] = $dview;
					// end send email
					
					$this->row++;
					$this->senddtr();
				// end 
			*/
		}
	}

