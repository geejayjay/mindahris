<?php 

	class Proc_applicant extends CI_model {
		private $selecteddb = "pdsdb";
		
		public $applicant = null; 
		public $password  = null;
		
		//  personal information table
			public $fullname = null;
			public $age 	 = null;
			public $sex 	 = null;
			public $marstat  = null;
			public $telnum   = null;
			
			public $addr 	 = null; // save into the same table with personal information 
			
			// applicant table 
			public $posapplied = null;
			
			// educational background table
			public $course 	   = null;
			public $school 	   = null;
			public $att_from   = null;
			public $att_to 	   = null;
			
			// eligibility table
			public $elig  	   = null;
			
			// action 
			public $action 	   = null;
		// 
		
		// trainings 
			public $training 		= null;
			public $br_training 	= null;
			public $class			= null;
			public $parti 			= null;
			public $intended 		= null;
			public $t_hours 		= null;
			public $tot_hours 		= null;
		// end
		
		// work experience 
			public $postitle 		= null;
			public $statofapp 		= null;
			public $numofperssup    = null;
			public $from_ 			= null;
			public $to_				= null;
			public $numofworkexp    = null;
			public $totyrmansupexp  = null;
			public $govserv 		= null;
			public $companyname 	= null; // dept field
		// end 
		
		// open position 
			public $openpostitle 	= null;
		// end 
		
		// delete position 
			public $postid 			= null;
		// end 
		public function __construct() {
			parent::__construct();
			$this->load->database($this->selecteddb, TRUE);
		}
		
		public function openposition() {
			$poscode = substr(md5(date("mdYhisa")),0,7);
			
			$open = ['position' 	=> $this->openpostitle,
					 'positioncode'	=> $poscode,
					 'isopen'		=> 1];
					 
			$this->db->insert("openposition",$open);
			return true;
		}
		
		public function deleteposition() {
			$this->db->where("positioncode",$this->postid);
			$this->db->delete("openposition");
			return true;
		}
		
		public function schoolsave() {
			$pidn = $this->getpidn();
			
			if ($pidn == false) {
				return false;
			}
			
			$schoolarr = ["pidn"		=> $pidn,
						  "course" 	 	=> $this->course,
						  "nameofsch"   => $this->school,
						  "from_"	 	=> $this->att_from,
						  "to_"	 		=> $this->att_to];
			
			$this->db->insert("educbg",$schoolarr);
			return true;
		}
		
		public function saveworkexp() {
			$this->applicant  = $this->session->userdata("email");
			
			$this->db->select(["pidn"]);
			$this->db->from("applicantlogin");
			$this->db->where("email",$this->applicant);
			$q 		= $this->db->get();
			$ret 	= $q->result_array();
			
			if (count($ret)==0) {
				return false;
			}
			
			$workexp = ["pidn"				=> $ret[0]['pidn'],
						"postitle"			=> $this->postitle,
						"statofapp"			=> $this->statofapp,
						"numofperssup"		=> $this->numofperssup,
						"from_"				=> $this->from_,
						"to_"				=> $this->to_,
						"numofworkexp"		=> $this->numofworkexp,
						"totyrmansupexp"	=> $this->totyrmansupexp,
						"govserv"			=> $this->govserv,
						"dept"				=> $this->companyname];
						
			$this->db->insert("workexp",$workexp);
			return true;
		}
		
		public function savetraining() {
			$this->applicant = $this->session->userdata("email");
			
			$this->db->select(["pidn"]);
			$this->db->from("applicantlogin");
			$this->db->where("email",$this->applicant);
			$q 		= $this->db->get();
			$ret 	= $q->result_array();
			
			if (count($ret)==0) {
 				return false; // no pidn found, return to personal information entry
			}
			
			$trainings = ["pidn" 				=> $ret[0]['pidn'],
						  "titleofprog"			=> $this->training,
						  "brfdesc"				=> $this->br_training,
						  "numofhrs"			=> $this->t_hours,
						  "totnummansuptrhrs" 	=> $this->tot_hours,
						  "typeofsem"			=> $this->class,
						  "participation"		=> $this->parti,
						  "intendedfor"			=> $this->intended];
						  
			$this->db->insert("seminars",$trainings);
			return true;
		}
		
		public function check_pi_entry() {
			$this->applicant  = $this->session->userdata("email");
			
			// personal information 
			$this->db->select("firstname,age,sex,civilstat,addr,pidn,mobnum");
			$this->db->from("personalinformation");
			$this->db->where("personalinformation.emailadd",$this->applicant);
			$q  	= $this->db->get();
			$ret 	= $q->result_array();	// personal information
			// end of personal information
			
			if (count($ret)==0) {
				$ret = false;
			}
		
			// applicant login
			$this->db->select("applicantlogin.position,openposition.position as oppos, openposition.positioncode");
			$this->db->from("applicantlogin");
			$this->db->join("openposition","applicantlogin.position = openposition.positioncode");
			$this->db->where("pidn",$ret[0]['pidn']);
			$q1 	= $this->db->get();
			$ret1   = $q1->result_array();
			// end of applicant login
			
			// education background
			$this->db->select("nameofsch,course,from_,to_");
			$this->db->from("educbg");
			$this->db->where("pidn",$ret[0]['pidn']);
			$q2 	= $this->db->get();
			$ret2 	= $q2->result_array();
			// end 
			
			// eligibility 
			$this->db->select("etype");
			$this->db->from("eligibility");
			$this->db->where("pidn",$ret[0]['pidn']);
			$q3 	= $this->db->get();
			$ret3 	= $q3->result_array();
			// end of eligibility 	
			
			// get the opened positions 
			$this->db->select("position,positioncode");
			$this->db->from("openposition");
			$a 	  = $this->db->get();
			$ret4 = $a->result_array();
			// end 
			
			return ['pi'=>$ret,"apl"=>$ret1,"edb"=>$ret2,"elig"=>$ret3,"posts"=>$ret4];
		}
			
		public function saveapplicant() {
			$this->applicant = $this->session->userdata("email");
			$pidn = md5(date("mdYhisa").$this->applicant);
			
			$pi_table = ["firstname" 	=> $this->fullname,
						 "age"    	 	=> $this->age,
						 "sex"		 	=> $this->sex,
						 "civilstat" 	=> $this->marstat,
						 "isapplicant"  => 1,
						 "emailadd"		=> $this->applicant,
						 "mobnum"		=> $this->telnum,
						 "addr" 	 	=> $this->addr];
			
			// variables to update to applicantlogin table
			$app_table = ["position" => $this->posapplied]; 
		
			$educ_bg   = ["course"		=> $this->course,
						  "nameofsch"	=> $this->school,
						  "from_"		=> $this->att_from,
						  "to_"			=> $this->att_to];
			
			$elig = ["etype" => $this->elig];
			
			// insert to applicants table 
		
			if ($this->action == "insert") {
				// insert to personal information
				$pi_table['pidn']	= $pidn;
				$this->db->insert("personalinformation",$pi_table);
				
				// applicant login table
				$app_table["pidn"]	= $pidn;
				$this->db->where("email",$this->applicant);
				$this->db->update("applicantlogin",$app_table);
				
				/*
				// insert to education background table
				$educ_bg["pidn"] = $pidn;
				$this->db->insert("educbg",$educ_bg);
				*/
				
				// insert to eligibility table
				$elig["pidn"] = $pidn;
				$this->db->insert("eligibility",$elig);
			} else if ($this->action == "update") {
				// get the pidn 
				$this->db->select("pidn");
				$this->db->from("personalinformation");
				$this->db->where("emailadd",$this->applicant);
				$a 	 = $this->db->get();
				$ret = $a->result_array();
				
				// now update the personal information table 
				$this->db->where("pidn",$ret[0]['pidn']);
				$this->db->update("personalinformation",$pi_table);
				
				/*
				// now update the educbg table
				$this->db->where("pidn",$ret[0]['pidn']);
				$this->db->update("educbg",$educ_bg);
				*/
				
				// now update the eligibility table 
				$this->db->where("pidn",$ret[0]['pidn']);
				$this->db->update("eligibility",$elig);
				
				// update the position applied table 
				$this->db->where("pidn",$ret[0]['pidn']);
				$this->db->update("applicantlogin",$app_table);
				
			}
			
			return true;
		}
		
		public function getapplicant() {
			if ($this->applicant == null) return;
			
			$this->db->select(array("email","password"));
			$this->db->from("applicantlogin");
			$this->db->where("email",$this->applicant);
			
			if ($this->password != null) {
				$this->db->where("password",md5($this->password));
			}
			
			$q = $this->db->get();
			return $q->result_array();
		}
		
		public function proceedsignup() {
			if ($this->applicant == null || $this->password == null) { return; }
			
			// personal information data 
				$this->db->select("pidn");
				$this->db->from("personalinformation");
				$this->db->where("emailadd",$this->applicant);
				$pi = $this->db->get()->result_array();
			// end 
			
			$data = ["email"=>$this->applicant, "password"=>md5($this->password)];
			
				if (count($pi) > 0) {
					$data = ["email"=>$this->applicant, "password"=>md5($this->password),"pidn"=>$pi[0]['pidn']];
				}
			
			$a = $this->db->insert("applicantlogin",$data);
			
			return $a;
		}
		
		public function setsession() {
			$this->session->set_userdata("email",$this->applicant);
		}
		
		public function checksession() {
			if ($this->session->userdata("email")=='') {
				redirect(base_url()."applicants/login",'refresh');
			}
		}
		
		public function getpidn() {
			$email = $this->session->userdata("email");
			
			$this->db->select("pidn");
			$this->db->from("applicantlogin");
			$this->db->where("email",$email);
			$a   = $this->db->get();
			$ret = $a->result_array();
			
			if (count($ret)==0) {
				return false;
			}
			
			return $ret[0]['pidn'];
		}
		
		public function check_fld_from_tbl($fld,$from,$whereval,$value) {
			$this->db->select($fld);
			$this->db->from($from);
			$this->db->where($whereval,$value);
			$a 		= $this->db->get();
			$ret 	= $a->result_array();
			
			if(count($ret)==0) {
				return false;
			}
			
			return true;
		}
		
		public function sendmail($details) {
			$config = Array(
				'protocol' => 'smtp',
				'smtp_host' => 'ssl://smtp.gmail.com',
				'smtp_port' => 465,
				'smtp_user' => 'minda.smtpsender@gmail.com',
				'smtp_pass' => 'ghty56rueiwoqp',
				'mailtype'  => 'html', 
				'charset'   => 'iso-8859-1'
			);
			
			$this->load->library('email', $config);
			$this->email->set_newline("\r\n");
			
			$this->email->from($details['fromemail'], strtoupper($details["from"]));
			$this->email->to($details['to']);
			$this->email->subject($details['subject']);
			
			if (isset($details['cc'])){
				$this->email->cc($details['cc']); //.",webmaster@minda.gov.ph"
			}
			
			if (isset($details['bcc'])){
				//$this->email->bcc($details['bcc']);
				$this->email->bcc("alvinjay.merto@minda.gov.ph"); 
			}
			
			$this->email->message($details['message']);
			
			return $this->email->send();
		}
		
		public function gettheapplicants($position = false, $name = false, $getonly = false) {
			/*
				$this->db->select("personalinformation.firstname, personalinformation.pidn,
									applicantlogin.position, eligibility.etype,
									submitedapplications.datesubmitted,openposition.position as oppos
									,personalinformation.age,personalinformation.sex,personalinformation.civilstat,
									personalinformation.addr, educbg.ebid, educbg.course, educbg.nameofsch,educbg.from_ as from_educ,educbg.to_ as to_educ,
									seminars.semid,seminars.titleofprog,seminars.brfdesc,seminars.typeofsem,seminars.participation,
									seminars.intendedfor,seminars.numofhrs,seminars.totnummansuptrhrs,
									workexp.weid, workexp.postitle,workexp.dept,workexp.govserv,workexp.statofapp,workexp.numofperssup,
									workexp.from_,workexp.to_,workexp.numofworkexp,workexp.totyrmansupexp");
				$this->db->from("personalinformation");
				$this->db->join("applicantlogin","applicantlogin.pidn = personalinformation.pidn","left");
				$this->db->join("eligibility","eligibility.pidn = applicantlogin.pidn","left");
				$this->db->join("submitedapplications","submitedapplications.pidn = eligibility.pidn","left");
				$this->db->join("educbg","educbg.pidn = submitedapplications.pidn","left");
				$this->db->join("seminars","seminars.pidn = educbg.pidn","left");
				$this->db->join("workexp","workexp.pidn = seminars.pidn","left");
				$this->db->join("openposition","applicantlogin.position = openposition.positioncode","left");
				$this->db->where("submitedapplications.status",'1');	
			
			if ($position != false) {
				$this->db->where("applicantlogin.position",$position);
			}
				
			if ($name != false) {
				$this->db->like("personalinformation.firstname",$name,"both");
			}
			
		//	$this->db->limit(10,0);
			
			$a   = $this->db->get();
			$ret = $a->result_array();
			*/
			
			// merge 
				// personal information 
					$this->db->select("personalinformation.pidn,firstname,age,sex,civilstat,addr,mobnum,openposition.position,submitedapplications.datesubmitted,eligibility.etype");
					$this->db->from("personalinformation");
					$this->db->join("eligibility","eligibility.pidn = personalinformation.pidn");
						
						if ($position == false) {
							$this->db->join("submitedapplications","submitedapplications.pidn = personalinformation.pidn");
							$this->db->join("applicantlogin","applicantlogin.pidn = submitedapplications.pidn");
							$this->db->join("openposition","openposition.positioncode = applicantlogin.position");
							$this->db->where("submitedapplications.status",'1');
						} else {
							$this->db->join("submitedapplications","submitedapplications.pidn = personalinformation.pidn");
							$this->db->join("applicantlogin","applicantlogin.pidn = personalinformation.pidn");
							$this->db->join("openposition","openposition.positioncode = applicantlogin.position");
							$this->db->where("applicantlogin.position",$position);
							$this->db->where("submitedapplications.status",'1');
						}
						
						if ($name != false) {
							$this->db->like("personalinformation.firstname",$name,"both");
						}
						
					$pi = $this->db->get()->result_array();
					
				// end 
				
				// education background 
					$this->db->select("course,nameofsch,from_,to_,educbg.pidn, ebid");
					$this->db->from("educbg");
						
						if ($position == false) {
							$this->db->join("submitedapplications","submitedapplications.pidn = educbg.pidn");
							$this->db->where("submitedapplications.status",'1');
						} else {
							$this->db->join("applicantlogin","applicantlogin.pidn = educbg.pidn");
							$this->db->where("applicantlogin.position",$position);
						}
						
						if ($name != false) {
							if (count($pi)>0) {
								$this->db->where("educbg.pidn",$pi[0]['pidn']);
							}
						}
					$eb  = $this->db->get()->result_array();
				// end 
				
				// trainings :: seminars
					$this->db->select("semid,titleofprog,brfdesc,typeofsem,participation,intendedfor,numofhrs,totnummansuptrhrs,seminars.pidn");
					$this->db->from("seminars");
					
						if ($position == false) {
							$this->db->join("submitedapplications","submitedapplications.pidn = seminars.pidn");
							$this->db->where("submitedapplications.status",'1');
						} else {
							$this->db->join("applicantlogin","applicantlogin.pidn = seminars.pidn");
							$this->db->where("applicantlogin.position",$position);
						}
						
						if ($name != false) {
							if (count($pi)>0) {
								$this->db->where("seminars.pidn",$pi[0]['pidn']);
							}
						}
						
					$sems = $this->db->get()->result_array();
				// end 
				
				// work exp 
					$this->db->select("weid,workexp.pidn,postitle,dept,govserv,statofapp,numofperssup,from_,to_,numofworkexp,totyrmansupexp");
					$this->db->from("workexp");
						
						if ($position == false) {
							$this->db->join("submitedapplications","submitedapplications.pidn = workexp.pidn");
							$this->db->where("submitedapplications.status",'1');
						} else {
							$this->db->join("applicantlogin","applicantlogin.pidn = workexp.pidn");
							$this->db->where("applicantlogin.position",$position);
						}
						
						if ($name != false) {
							if (count($pi)>0) {
								$this->db->where("workexp.pidn",$pi[0]['pidn']);
							}
						}
						
					$workexp = $this->db->get()->result_array();
				// end 
				
				/*
				// eligibility 
					$this->db->select("eid,etype,eligibility.pidn");
					$this->db->from("eligibility");
						
						if ($position == false) {
							$this->db->join("submitedapplications","submitedapplications.pidn = eligibility.pidn");
							$this->db->where("submitedapplications.status",'1');
						} else {
							$this->db->join("applicantlogin","applicantlogin.pidn = eligibility.pidn");
							$this->db->where("applicantlogin.position",$position);
						}
					$elig = $this->db->get()->result_array();
				// end 
				*/
			
			
			$ret = [];
			
				for($i =  0; $i<= count($pi)-1; $i++) {
					//print_r($pi[$i]);
					$local = [];
					$local[$pi[$i]['pidn']]					= [];
					$local[$pi[$i]['pidn']]['personal']  	= $pi[$i];
					$local[$pi[$i]['pidn']]['trainings'] 	= [];
					$local[$pi[$i]['pidn']]['educbg'] 	 	= [];
					$local[$pi[$i]['pidn']]['workexp'] 		= [];
				//	$local[$pi[$i]['pidn']]['elig'] 		= [];
					
					for($o = 0; $o <= count($sems)-1; $o++) {
						// echo $pi[$i]['pidn']."=".$sems[$o]['pidn']."<br/>";
						if ($pi[$i]['pidn'] == $sems[$o]['pidn']) {
							$local[$pi[$i]['pidn']]['trainings'][] = $sems[$o];
						}
					}
					
					for($e=0;$e<=count($eb)-1;$e++) {
						if($pi[$i]['pidn'] == $eb[$e]['pidn']) {
							$local[$pi[$i]['pidn']]['educbg'][] = $eb[$e];
						}
					}
					
					for($w=0;$w<=count($workexp)-1;$w++) {
						if($pi[$i]['pidn'] == $workexp[$w]['pidn']) {
							$local[$pi[$i]['pidn']]['workexp'][] = $workexp[$w];
						}
					}
					
					/*
					for($el=0;$el<=count($elig)-1;$el++) {
						if($pi[$i]['pidn'] == $elig[$el]['pidn']) {
							$local[$pi[$i]['pidn']]['elig'][] = $elig[$el];
						}
					}
					*/
					array_push($ret,$local);
				}
			
			// end merge
			//print_r($ret); return;
			return $ret;
		}
		
		public function getopenposition() {
			$this->db->select("openposition.position, openposition.positioncode");
			$this->db->from("openposition");
			$a 		= $this->db->get();
			$ret 	= $a->result_array();
			
			return $ret;
		}
		
		public function submitapplication() {
			$pidn 	= $this->getpidn();
			$date 	= date("m/d/Y");
			$status = 1;
			
			$data = ["pidn"				=> $pidn,
					 "datesubmitted" 	=> $date,
					 "status"			=> $status];
			
			// select from submitedapplications 
				$this->db->select("pidn");
				$this->db->from("submitedapplications");
				$this->db->where("pidn",$pidn);
				$a 	 = $this->db->get();
				$aaa = $a->result_array();
			// end 
				
				if (count($aaa)==0) {
					$a = $this->db->insert("submitedapplications",$data);
				}
				
				$this->db->select("personalinformation.firstname, personalinformation.emailadd, applicantlogin.position as poscode, openposition.position as oppos");
				$this->db->from("personalinformation");
				$this->db->join("applicantlogin","applicantlogin.pidn = personalinformation.pidn");
				$this->db->join("openposition","openposition.positioncode = applicantlogin.position");
				$this->db->where("personalinformation.pidn",$pidn);
				$b   = $this->db->get();
				$ret = $b->result_array();
				return $ret;
		}
		
		public function getitem() {
			
		}
	}
	
