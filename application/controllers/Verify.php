<?php 

	class Verify extends CI_Controller {
		public function index() {
			
			$data = null;
			if (isset($_POST['thebarcode'])) {
				$barcode = $_POST['thebarcode'];
				$categ	 = $_POST['categorytype'];
				if (strlen($barcode) == 0) {
					echo "Please enter barcode number.";
				} else {
					$this->load->model("v2main/Globalproc");
					
					// if ps
					$sql = null;
					switch($categ) {
						case "ps":
						case "paf":
								list($approvalid,$approver) = explode("-",$barcode);
								
								$sql = "select 
											ca.division_chief_id,
											ca.division_chief_is_approved,
											ca.leave_authorized_is_approved,
											ce.checkdate,
											e.f_name,
											eee.*
										from checkexact_approvals as ca 
										JOIN checkexact as ce on ca.exact_id = ce.exact_id 
										JOIN employees as e on ce.employee_id = e.employee_id 
										JOIN (select ee.f_name as app_name, ee.employee_id from employees as ee where employee_id = '{$approver}') as eee 
											on eee.employee_id = ca.division_chief_id
										where ca.checkexact_approval_id = '{$approvalid}'";
								/*		
								$ps = $this->Globalproc->__getdata($sql);
								if (count($ps)>0) {
									foreach($ps as $p) {										
										$data['name'][]  = $p->f_name;
										$data['date'][]  = date("F d, Y",strtotime($p->checkdate));
										// $data['recom'][] = $p->app_name;
										$stat = null;
										if ($p->division_chief_is_approved) {
											$stat = "APPROVED";
										} else {
											$stat = "NOT APPROVED";
										}
										$data['recom'][] = $stat;
										$data['appr'][]  = "-";
									}
								} else {
									$data = false;
								}
								*/
							break;
							
						case "lvcto":
							@list($first,$second,$approver) = explode("-",$barcode);
							
							if (strlen($approver) == 0) {
								// echo "I can't find the approver. Please enter the entire code. Thank you.";
							}
							
							$grpid = $first."-".$second;
							
							$sql = "select 
										ce.checkdate,
										e.f_name
										from checkexact as ce 
									JOIN employees as e 
										on ce.employee_id = e.employee_id
									where ce.grp_id = '{$grpid}'";
							
							break;
						case "ot":
							@list($exact, $approver) = explode("-",$barcode);
							
							$sql = "select 
										e.f_name,
										ot.ot_checkdate as checkdate
									from checkexact_ot as ot
										join employees as e on 
									ot.employee_id = e.employee_id 
										where ot.checkexact_ot_id = '{$exact}'";
							break;
						case "dtr":
							// 112120180806-52-27-b21c6314a-9171-126
							@list($f,$s,$t,$fr,$ff,$sx) = explode("-",$barcode);
							
							$code = $f."-".$s."-".$t."-".$fr;
							
							$sql = "select 
										e.f_name,
										cs.approval_status,
										dsr.dtr_coverage as coverdate
									from countersign as cs
										JOIN dtr_summary_reports as dsr 
									on cs.dtr_summary_rep = dsr.sum_reports_id
										JOIN employees as e 
									on cs.emp_id = e.employee_id
										where cs.vercode = '{$code}'";
						
							break;
							/*
						case "paf":
							@list($approvalid, $approver) = explode("-",$barcode);
							
							$sql = "select ";
							break;
							*/
					}
					
					@$q = $this->Globalproc->__getdata($sql);
					
						if (count($q)>0) {
							foreach($q as $p) {
								$data['name'][]  = $p->f_name;
								
								if (isset($p->checkdate)) {
									$data['date'][]  = date("F d, Y",strtotime($p->checkdate));
								} else if (isset($p->coverdate)){
									// $data['date'][]  = date("F d, Y",strtotime($p->coverdate));
									@list($from, $to) = explode("-",$p->coverdate);
									$data['date'][]  = date("F d, Y",strtotime($from))." - ".date("F d, Y", strtotime($to));
								}
								
									// $data['recom'][] = $p->app_name;
								$stat = null;
								if (@$p->division_chief_is_approved) {
									$stat = "APPROVED";
								} else {
									$stat = "NOT APPROVED";
								}
								$data['recom'][] = $stat;
								
								if (@$p->leave_authorized_is_approved) {
									$data['appr'][]  = "APPROVED";
								} else {
									$data['appr'][]  = "NOT YET APPROVED";
								}
								
							}
						} else {
							$data = false;
						}
					
					$data['ct'] = $categ;
					$data['bc'] = $barcode;
				}
				
				
			}
			
			/*
			for($i=0;$i<=3;$i++) {
				$data['name'][]  = "sample ".$i;
				$data['date'][]  = "date ".$i;
				$data['recom'][] = "recom ".$i;
				$data['appr'][]  = "approve ".$i;
			}
			*/
			
			$h['data'] = $data;
			$this->load->view("v2views/verify",$h);
		}
		
		public function travelorder() {
			$this->load->model("v2main/Globalproc");
			
			$data['ct']	= "to";
			$data['bc'] = $cn = (isset($_POST['thebarcode']))?$_POST['thebarcode']:null;
			
			if (isset($_POST['thebarcode'])) {
				if (strlen($_POST['thebarcode']) > 0) {
					$sql = "select * from travelorders where controlno like '%{$cn}%'";
					@$q  = $this->Globalproc->__getdata($sql);
					
					if (count($q)>0) {
						foreach($q as $qq) {
							$data['name'][]   = "<strong>".$qq->controlno."</strong> <br/>".$qq->nameoftravs;
							$data['recom'][]  = null;
							$data['date'][]   = date("M. d, Y", strtotime($qq->dateoftrav))." - ".date("M. d, Y", strtotime($qq->dateoftrav_to));
						}
					} else {
						$data = false;
					}
				}
			}
			
			$h['data']  = $data;
			$this->load->view("v2views/verify",$h);
		}
	}

