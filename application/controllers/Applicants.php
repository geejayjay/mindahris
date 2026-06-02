<?php 
	
	class Applicants extends CI_controller {
		public function __construct() {
			parent::__construct();
			$this->load->model("applicant/Proc_applicant");
		}
		
		public function index() {
			// if no session, show login
			// else show the sign up
			$this->Proc_applicant->checksession();
			$a = $this->Proc_applicant->check_pi_entry();
			
			$msg 	= null;
			$stat 	= 1; 
			$entry  = null;
		
			if ($a['pi'] != false) {
				//$msg   = "There is an entry for your personal information.";
				$stat  = 0;
			}
			
			$entry = $a;
			
			if (isset($_POST['pi_update']) || isset($_POST['pi_submit'])) {
				$this->Proc_applicant->fullname 	= $_POST['fullname'];
				$this->Proc_applicant->age 			= $_POST['age'];
				$this->Proc_applicant->sex 			= $_POST['sex'];
				$this->Proc_applicant->marstat    	= $_POST['marstat'];
				$this->Proc_applicant->telnum       = $_POST['telnum'];
				
				$this->Proc_applicant->addr 		= $_POST['addr'];
			
				$this->Proc_applicant->posapplied 	= $_POST['posapplied'];
				
				/*
				$this->Proc_applicant->course 		= $_POST['course'];
				$this->Proc_applicant->school 		= $_POST['school'];
				$this->Proc_applicant->att_from 	= $_POST['att_from'];
				$this->Proc_applicant->att_to 		= $_POST['att_to'];
				*/
				
				$this->Proc_applicant->elig 		= $_POST['elig'];
				
				if (isset($_POST['pi_submit'])) {
					// insert 
					$this->Proc_applicant->action = "insert";
				} else if (isset($_POST['pi_update'])) {
					// update
					$this->Proc_applicant->action = "update";
				}
				
				$this->Proc_applicant->saveapplicant();
				$msg   	= "Your personal information has been saved.";
				header("Refresh:0");
			}
			
			$display = $this->load->view("applicants/personalinfo.php",["msg"=>$msg,"stat"=>$stat,"entry"=>$entry],true);
			
			$this->load->view("applicants/mainhead.php",["display"=>$display]);
		}
		
		public function trainings() {
			$this->Proc_applicant->checksession();
			
			$msg = null;
			if (isset($_POST['subtran'])) {
				$this->Proc_applicant->training 		= $_POST['thetraining'];
				$this->Proc_applicant->br_training 		= $_POST['brfdesctr'];
				$this->Proc_applicant->class 			= $_POST['classtcn'];
				$this->Proc_applicant->parti			= $_POST['parttcn'];
				$this->Proc_applicant->intended 		= $_POST['intendtcn'];
				$this->Proc_applicant->t_hours 			= $_POST['trhrs'];
				$this->Proc_applicant->tot_hours 		= $_POST['tottrhrs'];
				$ret = $this->Proc_applicant->savetraining();
				
				if ($ret == false) {
					$msg = "There is no personal information associated with this account.";
				} else {
					$msg = "Your training has been recorded. You can add another one. Thank you.";
				}
			}
			
			$trainings = $this->load->view("applicants/trainings.php",["msg"=>$msg],true);
			$this->load->view("applicants/mainhead.php",["display"=>$trainings]);
		}
		
		public function workexperiences() {
			$this->Proc_applicant->checksession();
			
			$msg = null;
			
			if (isset($_POST['workexpbtn'])) {
				$this->Proc_applicant->postitle 		= $_POST['postitle'];
				$this->Proc_applicant->statofapp 		= $_POST['statofapp'];
				$this->Proc_applicant->numofperssup		= $_POST['numofpersup'];
				$this->Proc_applicant->from_ 			= $_POST['inc_from'];
				$this->Proc_applicant->to_ 				= $_POST['inc_to'];
				$this->Proc_applicant->numofworkexp 	= $_POST['numofworkexp'];
				$this->Proc_applicant->totyrmansupexp	= $_POST['totnumofworkexp'];
				$this->Proc_applicant->govserv 			= $_POST['sectoropt'];
				$this->Proc_applicant->companyname 		= $_POST['companyname'];
				$ret = $this->Proc_applicant->saveworkexp();
				
				if ($ret == true) {
					$msg = "Your work experience has been saved. Please enter another one. Thank you.";
				} else if($ret == false) {
					$msg = "There is no personal information associated with this account.";
				}
			}
			
			$we = $this->load->view("applicants/workexperiences.php",['msg'=>$msg],true);
			$this->load->view("applicants/mainhead.php",["display"=>$we]);
		}
		
		public function save_school() {
			$course 	= $this->input->post("course_");
			$school 	= $this->input->post("school_");
			$att_from 	= $this->input->post("att_from_");
			$att_to 	= $this->input->post("att_to_");
			
			// schoolsave
			$this->load->model("applicant/Proc_applicant");
			$this->Proc_applicant->course 	= $course;
			$this->Proc_applicant->school 	= $school;
			$this->Proc_applicant->att_from = $att_from;
			$this->Proc_applicant->att_to 	= $att_to;
			$ret = $this->Proc_applicant->schoolsave();
			
			if ($ret) {
				echo json_encode(true);
			} else {
				echo json_encode(false);
			}
		}
		
		public function login() {
			$msg  = null;
			
			if (isset($_POST['logbtn'])) {
				$this->load->model("applicant/Proc_applicant");
				
				$this->Proc_applicant->applicant = $_POST['email'];
				$this->Proc_applicant->password = $_POST['password'];
				
				$a = $this->Proc_applicant->getapplicant();
				
				if (count($a)==0) {
					// nothing found
					$msg = "We found nothing of this account.";
				} else {
					$this->Proc_applicant->setsession();
					redirect(base_url()."applicants",'refresh');
				}
			}
			
			$login = $this->load->view("applicants/login.php",['msg'=>$msg],true);
			$this->load->view("applicants/mainhead.php",["display"=>$login]);
		}
		
		public function logoff() {
			//unset ($_SESSION[""]);
			$this->session->unset_userdata('email');
			redirect(base_url()."applicants/login",'refresh');
		}
		
		public function signup() {
			$msg  = null;
			$stat = false;
			if (isset($_POST['upbtn'])) {
				$this->load->model("applicant/Proc_applicant");
				
				$this->Proc_applicant->applicant = $_POST['email'];
				$this->Proc_applicant->password  = $_POST['password'];
					
				$a = $this->Proc_applicant->getapplicant();

				if (count($a)==0) {
					
					// proceed sign up

					$ret = $this->Proc_applicant->proceedsignup();
					
					if ($ret == true);
					
					$msg = "Your account has been created.";
					$stat = true;
				} else {
					// account existed
					
					$msg = "Someone with the same email already existed in our system. Please use another.";
					$stat = false;
				}
			}
			
			$login = $this->load->view("applicants/login.php",["signup"=>true,"msg"=>$msg,"stat"=>$stat],true);
			$this->load->view("applicants/mainhead.php",["display"=>$login]);
		}
		
		public function submit_application($proceed = '') {
			$this->Proc_applicant->checksession();

			$pidn = $this->Proc_applicant->getpidn();
			$a = $this->Proc_applicant->check_fld_from_tbl("pidn","personalinformation","pidn",$pidn);
			
			$ret = false;
			if ($a) {
				$ret = true;
				$b = $this->Proc_applicant->check_fld_from_tbl("pidn","educbg","pidn",$pidn);
				
				if ($b) {
					$ret = true;
					$c  = $this->Proc_applicant->check_fld_from_tbl("pidn","seminars","pidn",$pidn);
					
					if ($c) {
						$ret = true;
						$d = $this->Proc_applicant->check_fld_from_tbl("pidn","eligibility","pidn",$pidn);
						
						if ($d) {
							$ret = true;
							$e = $this->Proc_applicant->check_fld_from_tbl("pidn","workexp","pidn",$pidn);
							
							if ($e) {
								$ret = true;
							} else {
								$ret = false;
							}
						} else {
							$ret = false;
						}
					} else {
						$ret = false;
					}
				} else {
					$ret = false;
				}
			} else {
				$ret = false;
			}
			
			// 
			$msg  = null;
			$stat = null;
			
			$ret = true;
			if ($ret == false) {
				// do not allow submission
				$msg  = "You are not allowed to submit your application as there are fields in the form that you have not filled up with. 
						Please complete those to proceed with the submission. Thank you.";
				$stat = 0;
			} else {
				// allow submit
				$msg  = "Thank you. We assumed that you have inputted all your necessary information. Please click submit.";
				$stat = 1;
				
				if ($proceed) {
					$a = $this->Proc_applicant->submitapplication();
					
					if (count($a)>0) {
						$msg = "Success!!! Your application has been submitted.";
					
					// send email to HR
						// from 
						// to 
						// subject
						// message
						$details['from']		= "{$a[0]['firstname']}";
						$details['fromemail'] 	= "{$a[0]['emailadd']}";
						//$details['to']			= "alvinjay.merto@minda.gov.ph";
						$details['to']			= "hrapplicants@minda.gov.ph";
						$details['subject'] 	= "Applicant applying for the position of {$a[0]['oppos']}";
						$details['message'] 	= "Hi my name is {$a[0]['firstname']}, I am applying for the position of {$a[0]['oppos']}";
						$this->Proc_applicant->sendmail($details);
					}
				}
			}
			
			$subap = $this->load->view("applicants/submitapplication.php",['msg'=>$msg,'stat'=>$stat],true);
			$this->load->view("applicants/mainhead.php",['display'=>$subap]);
			
		}
		
		public function forgotpassword() {
			
			$msg = null;
			if (isset($_POST['forgot_btn'])) {
				$this->load->model("applicant/Proc_applicant");
				
				$this->Proc_applicant->applicant = $_POST['email_forgot'];
				$a = $this->Proc_applicant->getapplicant();
				
				if (count($a)>0) {
					$new_pass = substr(md5(date("mdYhisa").$_POST['email_forgot']),0,7);
					
					$update_login["password"]	= md5($new_pass);
					$this->db->where("email",$_POST['email_forgot']);
					$this->db->update("applicantlogin",$update_login);
					//echo $new_pass;
					
					$details['from']		= "MinDA Assessment";
					$details['fromemail'] 	= "noreply@minda.gov.ph";
					
					$details['to']			= $_POST['email_forgot'];
					$details['subject'] 	= "MinDA Applicant Assessment - New Password";
					$details['message'] 	= "Please use this password <strong> {$new_pass} </strong>";
					$this->Proc_applicant->sendmail($details);
					$msg = "We have sent your new password to your email address.";
				} else {
					// no email found
					$msg = "There is no email address found.";
				}
			}
			
			$forgotpass = $this->load->view("applicants/login.php",['forgotpass'=>"true","msg"=>$msg],true);
			$this->load->view("applicants/mainhead.php",["display"=>$forgotpass]);
		}
		
		
	}

