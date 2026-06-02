<?php 
	
	class Ajaxcall extends CI_Controller {
		public function getdetails() {
			$var = $this->input->post("getwhat");

			// get the details here 
				
			// end 

			$ret = [
					["seminar","2010-02-01","2011-12-21","24","technical","conductor"],
					["seminar","2010-02-01","2011-12-21","24","technical","conductor"],
					["seminar","2010-02-01","2011-12-21","24","technical","conductor"],
					["seminar","2010-02-01","2011-12-21","24","technical","conductor"],
					["seminar","2010-02-01","2011-12-21","24","technical","conductor"]
				   ];
			echo json_encode($ret);
		}

		public function loadwindow() {
			$this->load->database("pdsdb", TRUE);
			$getwhat = $this->input->post("getwhat");
			$getid 	 = $this->input->post("getid");
			
			// get the details here 

			// end 

			$info = [];
			
			$this->db->select("surname,firstname,midname");
			$this->db->from("personalinformation");
			$this->db->where("pidn",$getid);
			$a = $this->db->get()->result_array();
			$info = [$a[0]['surname'].", ".$a[0]['firstname'].", ".$a[0]['midname']];
			
			// can either be skills or training
			$ret = [];
			switch($getwhat) {
				case "address":
					$this->db->select("*");
					$this->db->from("addresses");
					$this->db->where("pidn",$getid);
					
					$r = $this->db->get()->result_array();
					
					$ret['permanent'] 	= [];
					$ret['residential'] = [];
					$ret['unspec'] 		= [];
					
					foreach($r as $rr) {
						if (array_key_exists($rr['addrtype'],$ret)) {
							$ret[$rr['addrtype']][] = [ "houseblklot"	=> $rr['houseblklot'],
													    "street"		=> $rr['street'],
													    "brgy"			=> $rr['brgy'],
													    "prov"			=> $rr['prov'],
														"zcode"			=> $rr['zcode'],
														"subdvill"		=> $rr['subdvill'],
														"city"			=> $rr['city']
														];
						} else {
							$ret[$rr['unspec']][] = [ "houseblklot"		=> $rr['houseblklot'],
													    "street"		=> $rr['street'],
													    "brgy"			=> $rr['brgy'],
													    "prov"			=> $rr['prov'],
														"zcode"			=> $rr['zcode'],
														"subdvill"		=> $rr['subdvill'],
														"city"			=> $rr['city']
														];
						}
					}
					
					$this->load->view("oldir/address",["ret"=>$ret,"info"=>$info]);
					break;
				case "training":
					$this->db->select("titleofprog,from_,to_,numofhrs,typeofsem,conductedby");
					$this->db->from("seminars");
					$this->db->where("pidn",$getid);
					
					$localinfo = $this->db->get()->result_array();
					
					foreach($localinfo as $li) {
						$ret[] = [$li['titleofprog'],$li['from_'],$li['to_'],$li['numofhrs'],$li['typeofsem'],$li['conductedby']];
					}
					
					$this->load->view("oldir/popuptable",["ret"=>$ret,"info"=>$info]);
					break;
					
				case "course":
					$this->db->select("educbgtype,nameofsch,course,from_,to_,hlevel_unitsearned,yeargrad,scho_honorrec");
					$this->db->from("educbg");
					$this->db->where("pidn",$getid);
					$r = $this->db->get()->result_array();
					
					$ret['gradstud']    = [];
					$ret['secondary']   = [];
					$ret['voctrd']      = [];
					$ret['elementary']  = [];
					$ret['college']     = [];
					$ret['unspec']      = [];
					
					foreach($r as $rr) {
						if (array_key_exists($rr['educbgtype'],$ret)) {
							$ret[$rr['educbgtype']][] = ["nameofsch" 			 => $rr['nameofsch'],
														 "course"	 			 => $rr['course'],
														 "from_"	 			 => $rr['from_'],
														 "to_"	 				 => $rr['to_'],
														 "hlevel_unitsearned"	 => $rr['hlevel_unitsearned'],
														 "yeargrad"	 			 => $rr['yeargrad'],
														 "scho_honorrec"	 	 => $rr['scho_honorrec']
														];
						} else {
							$ret[$rr['unspec']][] =     ["nameofsch" 			 => $rr['nameofsch'],
														 "course"	 			 => $rr['course'],
														 "from_"	 			 => $rr['from_'],
														 "to_"	 				 => $rr['to_'],
														 "hlevel_unitsearned"	 => $rr['hlevel_unitsearned'],
														 "yeargrad"	 			 => $rr['yeargrad'],
														 "scho_honorrec"	 	 => $rr['scho_honorrec']
														];
						}
					}
					
					$this->load->view("oldir/course",["ret"=>$ret,"info"=>$info]);
					break;
				case "skills":
					$this->db->select("theinfo,typeofoi");
					$this->db->from("otherinfo");
					$this->db->where("pidn",$getid);
					$r = $this->db->get()->result_array();
					
					$ret["miao"]   = [];
					$ret["ssh"]    = [];
					$ret["nadr"]   = [];
					$ret['unspec'] = [];
			
					foreach($r as $rr) {
						// $ret[$r['typeofoi']][] = $r['theinfo'];
						if ( array_key_exists( $rr['typeofoi'],$ret) ) {
							$ret[$rr['typeofoi']][] = $rr['theinfo'];
						} else {
							$ret["unspec"][] = $rr['theinfo'];
						}
					}
					
					$this->load->view("oldir/skills",["ret"=>$ret,"info"=>$info]);
					break;
			}

		}
	}
	
