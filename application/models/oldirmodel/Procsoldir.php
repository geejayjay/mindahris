<?php 
	
	class Procsoldir extends CI_Model{

		public $office    = null;
		public $division  = null;

		// searches :: not in use
			public	$name 	  		 = null;
			public  $contactnumber	 = null;
			public  $training	 	 = null;
			public  $coursedegree    = null;
			public  $expertiseskills = null;
		// end searches
			
		// search
			public $category  = null;
			public $thesearch = null;
			public $chrono    = null;
		// end 
		
		// used in summary 
			protected $count = 0;
			protected $cnt = [];
		//end 
		public function __construct() {
			parent::__construct();
			
		}
		
		public function getdivision() {
			if ($this->office == null) {return;}
			
			$this->load->database("sqlserver", TRUE);
			// get the divisions of office here 
			$offices = ["oc"=>"1",
						"pppdo"=>"2",
						"ipiro"=>"3",
						"ofas"=>"4",
						"amo"=>"5",
						"other"=>"6",
						"oed"=>"7",
						];
			
			$this->db->select("abbr");
			$this->db->from("Division");
			$this->db->where("DBM_Sub_Pap_Id",$offices[strtolower($this->office)]);
			$division = $this->db->get()->result_array();
			
			$dd = [];
			foreach($division as $d) {
				$dd[] = $d['abbr'];
			}
			
			return $dd;
			//	$division = ["PPPDO","PFD","KMD","PRD","PDD","All"];
			// end 

			//return $division;
		}

		public function displayemps() {
			if ($this->office == null) {return;}
			
			$ret = [];
			// DB query here
				if ($this->division == null) {
					// display all employees under the office
					$this->load->database("sqlserver", TRUE);
					$this->db->select("DBM_Sub_Pap_id,DBM_Sub_Pap_Desc");
					$this->db->from("DBM_Sub_Pap");
					$this->db->where("abbr_dbm",$this->office);
					$office = $this->db->get()->result_array();
					
					if (count($office)>0) {
						$this->db->select("employee_id,positions.position_name");
						$this->db->from("employees");
						$this->db->join("positions","positions.position_id = employees.position_id");
						
						$where = "";						
						$where .= "DBM_Pap_id = '{$office[0]['DBM_Sub_Pap_id']}'";
						$this->db->where($where);
						// $this->db->where("DBM_Pap_id",$office[0]['DBM_Sub_Pap_id']);
						$empids = $this->db->get()->result_array();
						
						$emps 	   = [];
						$positions = [];
						foreach($empids as $es) {
							$emps[] = "personalinformation.pidn = '".$es['employee_id']."'";
							$positions[$es['employee_id']] = $es['position_name'];
						}
					}
				} else {
					// display all employees under this division
					$this->load->database("sqlserver", TRUE);
					$this->db->select("Division_Id");
					$this->db->from("Division");
					$this->db->where("abbr",$this->division);
					$divid = $this->db->get()->result_array();
					
					if (count($divid)>0) {
						$this->db->select("employee_id,positions.position_name");
						$this->db->from("employees");
						$this->db->join("positions","positions.position_id = employees.position_id");
						$this->db->where("Division_id",$divid[0]['Division_Id']);
						$empids = $this->db->get()->result_array();
						
						$emps 	   = [];
						$positions = [];
						foreach($empids as $es) {
							$emps[] = "personalinformation.pidn = '".$es['employee_id']."'";
							$positions[$es['employee_id']] = $es['position_name'];
						}
					}
				}

					// jump to other database 
						$this->load->database("pdsdb", TRUE);
						$inf = "";
						if ($this->thesearch != null && $this->category != null) {
							$sql = "";
							switch($this->category) {
								case "training":									
									if ($this->chrono != true) {
										$titleofprog = explode(" ",$this->thesearch);
										$titleofprog = array_map(function($item){
											return "tbl1.titleofprog like '%{$item}%'";
										},$titleofprog);
										
										$sql = "select * from (select personalinformation.pidn,surname,firstname,midname,sex,telnum,mobnum, seminars.titleofprog
												from personalinformation join seminars on seminars.pidn = personalinformation.pidn 
												where ".implode(" or ",$emps).") as tbl1 where ".implode(" or ",$titleofprog)."";
									} else {
										$sql = "select * from (select personalinformation.pidn,surname,firstname,midname,sex,telnum,mobnum, seminars.titleofprog
												from personalinformation join seminars on seminars.pidn = personalinformation.pidn 
												where ".implode(" or ",$emps).") as tbl1 where tbl1.titleofprog like '%{$this->thesearch}%'";
									}
									break; // tbl1.titleofprog like '%{$this->thesearch}%'
								case "course":
									if ($this->chrono != true) {
										$course = explode(" ",$this->thesearch);
										$course = array_map(function($item){
											return "tbl1.course like '%{$item}%'";
										},$course);
										
										$sql = "select * from (select personalinformation.pidn,surname,firstname,midname,sex,telnum,mobnum, educbg.course
												from personalinformation join educbg on educbg.pidn = personalinformation.pidn 
												where ".implode(" or ",$emps).") as tbl1 where ".implode(" or ",$course)."";
									} else {
										$sql = "select * from (select personalinformation.pidn,surname,firstname,midname,sex,telnum,mobnum, educbg.course
												from personalinformation join educbg on educbg.pidn = personalinformation.pidn 
												where ".implode(" or ",$emps).") as tbl1 where tbl1.course like '%{$this->thesearch}%'";
									}
									break;
								case "skills":
									if ($this->chrono != true) {
											$skills = explode(" ",$this->thesearch);
											$skills = array_map(function($item){
												return "tbl1.theinfo like '%{$item}%'";
											},$skills);
											
											$sql = "select * from (select personalinformation.pidn,surname,firstname,midname,sex,telnum,mobnum, otherinfo.theinfo
												from personalinformation join otherinfo on otherinfo.pidn = personalinformation.pidn 
												where ".implode(" or ",$emps).") as tbl1 where ".implode(" or ",$skills)."";
									} else {
										$sql = "select * from (select personalinformation.pidn,surname,firstname,midname,sex,telnum,mobnum, otherinfo.theinfo
												from personalinformation join otherinfo on otherinfo.pidn = personalinformation.pidn 
												where ".implode(" or ",$emps).") as tbl1 where tbl1.theinfo like '%{$this->thesearch}%'";
									}
									break;
							}
							$inf = $this->db->query($sql)->result_array();
						} else {
							$this->db->select("personalinformation.pidn,surname,firstname,midname,sex,telnum,mobnum");
							$this->db->from("personalinformation");
							$where = implode(" or ",$emps);
							$this->db->where($where);
							$inf = $this->db->get()->result_array();
						}
						
						$inthebox = [];
						foreach($inf as $i) {
							if (!array_key_exists($i['pidn'],$inthebox)) {
							$ret[] = ["id" 		   => $i['pidn'],
									  "name"	   => $i['surname'].", ".$i['firstname'].", ".$i['midname'],
									  "position"   => $positions[$i['pidn']],
									  "sex"		   => $i['sex'],
									  "contactno"  => "<strong> Tel. No. </strong>".$i['telnum']." | <strong> mobile: </strong>".$i['mobnum']
									 ];
							}
							$inthebox[$i['pidn']] = true;
						}
				// end 
				
			// end searches
		/*
			$ret = [
					[
					 "id"		 => "389",
					 "name"		 => "Alvin Merto",
					 "division"  => "KMD",
					 "position"  => "Software programmer",
					 "sex" 		 => "male",
					 "address"	 => "Purok 5. Brgy. Gatungan, Bunawan District, Davao City",
					 "contactno" => "09097434684",
					 "course"    => "BS Computer Science"
					],
		*/
			return $ret;
		}
		
		public function summary($emps) {
			$this->load->model("pdsmodel/Mainprocs");
			$tbls = ["addresses",
					 "educbg",
				     "eligibility",
				     "identification",
				     "involvements",
					 "otherinfo",
					 "questiontbl",
					 "reference",
					 "seminars",
					 "workexp"];
					 
			$rets = [];
			foreach($emps as $key => $e) {
				$rets[$e['id']] = ["dbs"=>""];
				foreach($tbls as $t) {
					// $rets[$e][$t] = $this->Mainprocs->__getdata("select count(pidn) from pdsdb.dbo.{$t} where pidn = '{$e['id']}'");
					$rets[$e['id']]['dbs'][] = [$t=> count($this->Mainprocs->__getdata("select pidn from pdsdb.dbo.{$t} where pidn = '{$e['id']}'"))];
				}
			}
			return $rets;
		}
		
		public function returncount($tbl,$pidn,$count,$terminate) {
			// $this->load->database("pdsdb", TRUE);
			$this->load->model("pdsmodel/Mainprocs");
			
			$this->count++;
			$cnt[] = $this->Mainprocs->__getdata("select pidn from pdsdb.dbo.{$tbl} where pidn='{$pidn}'");
		
			/*
			$this->db->select("*");
			$this->db->from($tbl);
			$this->db->where("pidn",$pidn);
			$cnt[$tbl] = $this->db->get()->result_array();
			*/
			
			$this->summary();
			
			if ($terminate) {
				return $cnt;
			} 
		}
	}

