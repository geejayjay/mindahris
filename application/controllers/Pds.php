<?php 
	//use Libraries\PhpSpreadsheet\Spreadsheet;
	//use Libraries\PhpSpreadsheet\Writer\Xlsx;

	class Pds extends CI_Controller {
		public function __construct() {
			parent::__construct();
			$this->load->model("pdsmodel/Mainprocs");
		}
		
		public function index() {
			$data['content'] 			 = "pds/homepage.blade.php";
			$data['tabs']['bigtab']   	 = null;
			$data['tabs']['smalltab'] 	 = null;
		
			$userid   = $this->Mainprocs->get_pidn();
		
			if ($userid == null) {
				// not logged in
				$data['tabs']['notloggedin'] = true;
				$data['tabs']['nopdsinput']  = true;
			} else {
				// logged in
				// check for current data 
				$data['tabs']['notloggedin'] = false;
				$noinput = $this->Mainprocs->checkforinput();
				
				if (!$noinput) {
					$data['tabs']['nopdsinput'] = true;
				} else {
					$data['tabs']['nopdsinput'] = false;
				}
				
				$zeroes = [];
			
				$tables	 = ['personalinformation',
							'educbg',
							'familybg',
							'addresses',
							'eligibility',
							'workexp',
							'seminars',
							'questiontbl',
							'reference',
							'identification'];
				
				foreach($tables as $t) {
					$orderby = false;
					${$t} = $this->Mainprocs->fetch("pdsdb.dbo.".$t,['pidn'=>$userid],["*"],$orderby);
					
					if ( count(${$t}) == 0 ) {
						array_push($zeroes,$t);
					}
				}
				
				$data['zeroes'] = $zeroes;
				// var_dump($zeroes);
			}
			
			
			
			$this->load->view("pds/main.blade.php",$data);
		}
		
		public function c1($smalltab) {
			$this->load->model("pdsmodel/Mainprocs");
			$id 			 = $this->Mainprocs->get_pidn();
			
			$data['content'] = null;
			$tblselect       = null;
			
			$bypass 		 = false;
			
			$details 		 = [];
			switch($smalltab){
            	case "personalinformation":
            		$data['content'] = "pds/contents/c1/personalinformation.blade.php";
					$tblselect		 = "pdsdb.dbo.personalinformation";
					
					$details = [];
            		break;
            	case "familybackground":
            		$data['content'] = "pds/contents/c1/familybackground.blade.php";
					$tblselect		 = "pdsdb.dbo.familybg";
						
						// get the list of children 
						$loc['data'] = $this->Mainprocs->fetch("children",["pidn"=>$id],["*"]);
						$data['loc'] = $this->load->view("pds/contents/c1/pi_components/listofchildren",$loc,true);
            		break;
            	case "educationalbackground":
            		$data['content'] = "pds/contents/c1/educationalbackground.blade.php";
					$tblselect		 = "pdsdb.dbo.educbg";
						
						// educational background
						
						// $loc['data'] = $a = $this->Mainprocs->fetch("educbg",["educbgtype" => $loc['tbl'],"conn"=>"and","pidn"=>$id],["*"]);
						// $details = ['educbgtype'];
						
						// $loc['tbl']  = "elementary";
						// $loc['data'] = [];
						// $data['loc'] = $this->load->view("pds/contents/c1/pi_components/educbg",$loc,true);
						
						$bypass 	   = true;
						$data['loc']   = null;
             		break;
            }
			
			$data['tabs']['details']  = $dets = $this->Mainprocs->getinformation($tblselect);
		
			if ($bypass == false) {
				if (count($dets)==0) {
					// make new entry
					
					// ------- there is a ERROR when not logged in
					$newentry = $this->Mainprocs->makenewentry($tblselect, "true");
				}
			}
			
			$data['tabs']['bigtab']   	 = "c1";
			$data['tabs']['smalltab'] 	 = $smalltab;
		
			$data['tabs']['notloggedin'] = false;
			
			$this->load->view("pds/main.blade.php",$data);
		}
		
		public function c2($smalltab) {
			$this->load->model("pdsmodel/Mainprocs");
			$id 			 = $this->Mainprocs->get_pidn();
			
			$data['content'] = null;
			 switch($smalltab){
                    case "eligibility":
                        $data['content'] = "pds/contents/c2/eligibility.blade.php";
						$tblselect		 = "pdsdb.dbo.eligibility";
						
						$loc['data'] = $this->Mainprocs->fetch("eligibility",["pidn"=>$id],["*"]);
						$data['loc'] = $this->load->view("pds/contents/c2/c2_components/listofeligs",$loc,true);
                        break;
                    case "workexperience":
                        $data['content'] = "pds/contents/c2/workexp.blade.php";
						$tblselect		 = "pdsdb.dbo.workexp";
						
						$loc['data'] = $this->Mainprocs->fetch("workexp",["pidn"=>$id],["*"]);
						$data['loc'] = $this->load->view("pds/contents/c2/c2_components/workexp",$loc,true);
                        break;
			 }
			
			$data['tabs']['details']  = $dets = $this->Mainprocs->getinformation($tblselect);
			
			$data['tabs']['bigtab']   = "c2";
			$data['tabs']['smalltab'] = $smalltab;
		
			$data['tabs']['notloggedin'] = false;
			
			$this->load->view("pds/main.blade.php",$data);
		}
		
		public function c3($smalltab) {
			$this->load->model("pdsmodel/Mainprocs");
			$id 			 = $this->Mainprocs->get_pidn();
			
			$data['content'] = null;
			switch($smalltab){
                    case "involvement":
                        $data['content'] = "pds/contents/c3/involvement.blade.php";
						$tblselect		 = "pdsdb.dbo.eligibility";
						
						$loc['data'] = $this->Mainprocs->fetch("involvements",["pidn"=>$id],["*"]);
						$data['loc'] = $this->load->view("pds/contents/c3/c3_components/invs.php",$loc,true);
                        break;
                    case "trainings":
                        $data['content'] = "pds/contents/c3/trainings.blade.php";
						$tblselect		 = "pdsdb.dbo.seminars";
						
						$loc['data'] = $this->Mainprocs->fetch("seminars",["pidn"=>$id],["*"],"from_ DESC");
						$data['loc'] = $this->load->view("pds/contents/c3/c3_components/seminars.php",$loc,true);
                        break;
                    case "otherinfo":
                        $data['content'] = "pds/contents/c3/otherinfo.blade.php";
						$tblselect		 = "pdsdb.dbo.eligibility";
						
						$loc['data'] = $this->Mainprocs->fetch("pdsdb.dbo.otherinfo",["pidn"=>$id,"conn"=>"and","typeofoi"=>"ssh"],["*"]);
						$data['ss']  = $this->load->view("pds/contents/c3/c3_components/otherinfo.php",$loc,true);
						
						$loc['data']   = $this->Mainprocs->fetch("pdsdb.dbo.otherinfo",["pidn"=>$id,"conn"=>"and","typeofoi"=>"nadr"],["*"]);
						$data['nadr']  = $this->load->view("pds/contents/c3/c3_components/otherinfo.php",$loc,true);
						
						$loc['data']   = $this->Mainprocs->fetch("pdsdb.dbo.otherinfo",["pidn"=>$id,"conn"=>"and","typeofoi"=>"miao"],["*"]);
						$data['miao']  = $this->load->view("pds/contents/c3/c3_components/otherinfo.php",$loc,true);
                        break;
			}
			
			$data['tabs']['details']  = $dets = $this->Mainprocs->getinformation($tblselect);
			
			$data['tabs']['bigtab']   = "c3";
			$data['tabs']['smalltab'] = $smalltab;
			
			$data['tabs']['notloggedin'] = false;
			
			$this->load->view("pds/main.blade.php",$data);
		}
		
		public function c4($smalltab) {
			$this->load->model("pdsmodel/Mainprocs");
			$id 			 = $this->Mainprocs->get_pidn();
			
			$data['content'] = null;
			switch($smalltab){
                case "questionnaire":
                    $data['content'] 	= "pds/contents/c4/agreement.blade.php";
					
					/*
					$ddd = $this->Mainprocs->__getdata("select * from pdsdb.dbo.questions as q 
														join pdsdb.dbo.optiontbl as opt on q.qid = opt.qid 
														join pdsdb.dbo.choicetbl as ch on opt.optionid = ch.optionid");
				
					$questions = [];
					$options   = [];
					$choices   = [];
					
					foreach ($ddd as $d) {
						$questions[$d->qid] = $d->thequestion;
					}
					
					foreach($ddd as $d) {
						
					}
					
					var_dump($questions);
					var_dump($options);
				*/
					
					$data['data'] = $this->Mainprocs->fetch("questiontbl",["pidn" => $id],["*"]);
					
					break;
                case "references":
                    $data['content'] = "pds/contents/c4/references.blade.php";
					
					$loc['data'] = $this->Mainprocs->fetch("reference",["pidn"=>$id],["*"]);
					$data['loc'] = $this->load->view("pds/contents/c4/c4_components/references.php",$loc,true);
                    break;
                case "identification":
                    $data['content'] = "pds/contents/c4/identification.blade.php";
					
					$loc['data'] = $this->Mainprocs->fetch("identification",["pidn"=>$id],["*"]);
					$data['loc'] = $this->load->view("pds/contents/c4/c4_components/listofids.php",$loc,true);
					break;
            }
			
			$data['tabs']['bigtab']   = "c4";
			$data['tabs']['smalltab'] = $smalltab;
			
			$data['tabs']['notloggedin'] = false;
			
			$this->load->view("pds/main.blade.php",$data);
		}
		
		public function printpds($page = '') {
			$this->load->model("pdsmodel/Mainprocs");
			$id = $this->Mainprocs->get_pidn();
			
			// personal information 
			// $sql = "select * from personalinformation as pi JOIN familybg as fb on pi.pidn = fb.pidn where pi.pidn = '{$id}'";
			$sql = "select 
						pi.surname,
						pi.firstname,
						pi.midname,
						pi.nameext,
						pi.dateofbirth,
						pi.sex,
						pi.civilstat,
						pi.height,
						pi.weight,
						pi.bloodtype,
						pi.telnum as pitelnum,
						pi.mobnum,
						pi.emailadd,
						pi.gsisnum,
						pi.lovenum,
						pi.philhnum,
						pi.sssnum,
						pi.status,
						pi.placeofbirth,
						pi.tin,
						pi.empnum,
						pi.isfilipino,
						pi.bybirth,
						pi.bycit,
						pi.dualcity,
						fb.sp_surname,
						fb.sp_fname,
						fb.sp_n_ext,
						fb.sp_mname,
						fb.occupation,
						fb.empbname,
						fb.baddr,
						fb.telnum as fbtelnum,
						fb.fsurname,
						fb.ffirstname,
						fb.fnameext,
						fb.fmidname,
						fb.mmaidenname,
						fb.msurname,
						fb.mfirstname,
						fb.mmidname,
						fb.childid,
						fb.status
						from personalinformation as pi join
						familybg as fb on pi.pidn = fb.pidn where pi.pidn = '{$id}'";
			$pi  = $this->Mainprocs->__getdata($sql); 
			
			$tables	 = ['educbg',
						'addresses',
						'children',
						'eligibility',
						'workexp',
						'involvements',
						'seminars',
						'otherinfo',
						'questiontbl',
						'reference',
						'identification'];
			
			foreach($tables as $t) {
				$orderby = false;
				
				if ($t == "educbg") {
					$orderby = " poorder ASC";
				}
				
				${$t} = $this->Mainprocs->fetch("pdsdb.dbo.".$t,['pidn'=>$id],["*"],$orderby);
			}
			
			$firstpage  = ["pifb"=>$pi,"educbg"=>$educbg,"addr"=>$addresses,"chr"=>$children];
			$secondpage = ["el"=>$eligibility,"wp"=>$workexp];
			$thirdpage  = ["inv"=>$involvements,"sems"=>$seminars,"oi"=>$otherinfo];
			$fourthpage = ["qs"=>$questiontbl,"ref"=>$reference,"id"=>$identification];
			
			$this->load->view("pds/printables/firstpage.php",$firstpage);
			$this->load->view("pds/printables/secondpage",$secondpage);
			$this->load->view("pds/printables/thirdpage",$thirdpage);
			$this->load->view("pds/printables/fourthpage",$fourthpage);
		}
		
		public function printc1($fromrest=false, $iid=false) {
			$this->load->model("pdsmodel/Mainprocs");
			$id = $this->Mainprocs->get_pidn();
			
			if (strlen($id)==0) {
				$id = $iid;
			}
			
			// personal information 
		//$sql = "select * from personalinformation as pi JOIN familybg as fb on pi.pidn = fb.pidn where pi.pidn = '{$id}'";
			$sql = "select 
						pi.surname,
						pi.firstname,
						pi.midname,
						pi.nameext,
						pi.dateofbirth,
						pi.sex,
						pi.civilstat,
						pi.height,
						pi.weight,
						pi.bloodtype,
						pi.telnum as pitelnum,
						pi.mobnum,
						pi.emailadd,
						pi.gsisnum,
						pi.lovenum,
						pi.philhnum,
						pi.sssnum,
						pi.status,
						pi.placeofbirth,
						pi.tin,
						pi.empnum,
						pi.isfilipino,
						pi.bybirth,
						pi.bycit,
						pi.dualcity,
						fb.sp_surname,
						fb.sp_fname,
						fb.sp_n_ext,
						fb.sp_mname,
						fb.occupation,
						fb.empbname,
						fb.baddr,
						fb.telnum as fbtelnum,
						fb.fsurname,
						fb.ffirstname,
						fb.fnameext,
						fb.fmidname,
						fb.mmaidenname,
						fb.msurname,
						fb.mfirstname,
						fb.mmidname,
						fb.childid,
						fb.status
						from personalinformation as pi join
						familybg as fb on pi.pidn = fb.pidn where pi.pidn = '{$id}'";
			$pi  = $this->Mainprocs->__getdata($sql);
			
			$tables = [
				"educbg","addresses","children"
			];
			
			foreach($tables as $t) {
				$orderby = false;
				
				if ($t == "educbg") { $orderby = " poorder ASC"; }
				${$t} = $this->Mainprocs->fetch("pdsdb.dbo.".$t,['pidn'=>$id],["*"],$orderby);
			}
			
			$firstpage  = ["pifb"=>$pi,"educbg"=>$educbg,"addr"=>$addresses,"chr"=>$children];
			
			if (!$fromrest) {
				$this->load->view("pds/printables/firstpage.php",$firstpage);
			} else {
				return $firstpage;
			}
		}
		
		public function printc2($fromrest = false,$iid=false) {
			$this->load->model("pdsmodel/Mainprocs");
			$id = $this->Mainprocs->get_pidn();
			
			if (strlen($id)==0) {
				$id = $iid;
			}
			
			$tables = ["eligibility","workexp"];
			
			foreach($tables as $t) {
				$orderby = false;
				
				if ($t == "workexp") {
					$orderby = "from_ DESC";
				}
				
				if ($t=="eligibility") {
					$orderby = "";
				}
				
				${$t} = $this->Mainprocs->fetch("pdsdb.dbo.".$t,['pidn'=>$id],["*"],$orderby);
			}
			
			$secondpage = ["el"=>$eligibility,"wp"=>$workexp];
			
			if (!$fromrest) {
				$this->load->view("pds/printables/secondpage",$secondpage);
			} else {
				return $secondpage;
			}
		}
		
		public function printc3($fromrest = false, $iid=false) {
			$this->load->model("pdsmodel/Mainprocs");
			$id = $this->Mainprocs->get_pidn();
			
			if (strlen($id)==0) {
				$id = $iid;
			}
			
			$tables = ['involvements',
						'seminars',
						'otherinfo'];
			
			foreach($tables as $t) {
				$orderby = false;
				
				if ($t == "seminars") {
					$orderby = "from_ DESC";
				}
				
				if ($t == "involvements") {
					$orderby = "from_ DESC";
				}
				
				${$t} = $this->Mainprocs->fetch("pdsdb.dbo.".$t,['pidn'=>$id],["*"],$orderby);
			}
			
			$thirdpage  = ["inv"=>$involvements,"sems"=>$seminars,"oi"=>$otherinfo];
			
			if (!$fromrest) {
				$this->load->view("pds/printables/thirdpage",$thirdpage);
			} else {
				return $thirdpage;
			}
		}
		
		public function printc4($fromrest = false, $iid=false) {
			$this->load->model("pdsmodel/Mainprocs");
			$id = $this->Mainprocs->get_pidn();
			
			if (strlen($id)==0) {
				$id = $iid;
			}
			
			$tables = ['questiontbl',
					   'reference',
					   'identification'];

			foreach($tables as $t) {
				$orderby = false;
				
				${$t} = $this->Mainprocs->fetch("pdsdb.dbo.".$t,['pidn'=>$id],["*"],$orderby);
			}
	
			$fourthpage = ["qs"=>$questiontbl,"ref"=>$reference,"id"=>$identification];
			
			if (!$fromrest) {
				$this->load->view("pds/printables/fourthpage",$fourthpage);
			} else {
				return $fourthpage;
			}
		}
		
		public function separatec1() {
			$this->load->view("pds/printables/separatesheets/edulist.php");
		}
			
		public function administration() {
			$this->load->model("v2main/Globalproc");
			
			$data = $this->Globalproc->gdtf("employees",["status"=>"1","conn"=>"and","employment_type"=>"regular"],"*");
			
			// var_dump($data);
			$this->load->view("pds/hradmin.php");
		}
		
		public function applicants($applyingfor = '', $what = '', $postid = '') {
			$data['title']						= "List of Applicants";
			$data['main_content']				= "applicants/listapplicants.php";
			$data['admin']						= true;
			$data['headscripts']['style'][0]	= base_url()."v2includes/style/applicants/apps_dashboard.style.css";
			$data['headscripts']['js'][0]		= base_url()."v2includes/js/applicant/proc_applicants.js";
			
			$this->load->model("applicant/Proc_applicant");
			$openpos = $this->Proc_applicant->getopenposition();
			
			if ($applyingfor == "applying" && $what) {
				// clicked on the positions opened
				$ret 	 = $this->Proc_applicant->gettheapplicants($what);	
				
			} else {
				$ret 	 = $this->Proc_applicant->gettheapplicants();
			
				// $ret = [];
				if (!isset($_POST['namesearch'])) {
					//$data['display_as'] = "names";
				}
			}
			
			$bypass = false;
			if (isset($_POST['namesearch'])) {
				// searching for names 
				$ret 	 				= $this->Proc_applicant->gettheapplicants(false, $_POST['namesearch']);
				$data['searchfor']		= $_POST['namesearch'];
				unset($data['display_as']);
				$bypass = true;
				// end 
			}
			
				if (!isset($_GET['display'])) {
					if ($bypass == false) {
						$data['display_as'] = "names";
					}
				} else {
					if ($_GET['display'] == "names") {
						$data['display_as'] = "names";
					} else if ($_GET['display'] == "table") {
						unset($data['display_as']);
					}
				}
			
			// open position tab is opened 
			if ($applyingfor == "openposition") {
				
				if (isset($_POST['openpostbtn'])) {
					$this->Proc_applicant->openpostitle = $_POST['postitle'];
					$a 		= $this->Proc_applicant->openposition();
					$data['openmsg']	= "Position has been opened.";
					header("Refresh:0");
				}
				
				if ($what == 'delete') {
					$this->Proc_applicant->postid 		= $postid;
					$a = $this->Proc_applicant->deleteposition();
					redirect(base_url().'/pds/applicants/openposition','refresh');
				}
				
				$data['displayopen']	= true;
			}
			// end 
			
		//	$ret  = [];
			
			$data['values']	  = $ret;
			$data['openpos']  = $openpos;
			$this->load->view('hrmis/admin_view',$data);
		}
		
		public function getdatafromdb($id) {
			header("Access-Control-Allow-Origin: *");
			header("Content-type: application/json; charset=UTF-8");
				
			echo json_encode(["firstpage"  => $this->printc1(true,$id),
							  "secondpage" => $this->printc2(true,$id),
							  "thirdpage"  => $this->printc3(true,$id),
							  "fourthpage" => $this->printc4(true,$id)]);
			
		}
		
		/*
		public function toexcel() {
			$this->load->library('PhpSpreadsheet\class_name');
			
			$fileName = 'assets/pds/pdsfiles/pds.xlsx'; 
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setCellValue('A1', 'Id');
			$sheet->setCellValue('B1', 'Name');
			
			$writer = new Xlsx($spreadsheet);
			$writer->save("upload/".$fileName);

			header("Content-Type: application/vnd.ms-excel");
			
			redirect(base_url()."/upload/".$fileName);      
		}
		*/
	}
