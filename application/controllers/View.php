<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	
	class View extends CI_Controller{		
	
		public function __construct() {
			parent::__construct();
		}
		
		public function form(){
			$b_url = base_url();
			if ($this->session->userdata("is_logged_in") != true){
				echo "<p style='text-align:center; padding:60px; background:#e2e2e2;font-family: arial;'> 
						You are not logged in. Click <a href='{$b_url}' target='_blank'> here </a> to log in; when you're done. Go back to this page and refresh. 
					  </p> ";
				return;
			}
			
			$this->load->model("v2main/Globalproc");
			$this->load->model("admin_model");
			$grp_id = $this->uri->segment(3);
			
			// generic form except special leave 
			// sick leave 
			// SPL
			// paf 
			// ps 
			// ot 
			// cto :: check this
			
			/*
				if not leave dont refer to checkleave_logs
			*/
			
			
			
			$emp 				 = $this->session->userdata('employee_id');
			$data['Division_id'] = $this->session->userdata('division_id');
			$data['ischief']	 = $this->Globalproc->is_chief("division",$emp);
			$data['isdbm']	 	 = $this->Globalproc->is_chief("director",$emp);
			
			$lv_value = $this->Globalproc->gdtf("checkexact",
												['grp_id'=>$grp_id],
												"*");
			
			$typeof_form    = $lv_value[0]->leave_id;
			
			$browsed_emp_id = null;
			$use_the_form   = null;
			
			$theinfo = Array();
			
			if ($typeof_form != 0) { // generic, sick and spl
				switch($typeof_form) {
					case "1": // sick
						$data['headscripts']['style'][] = base_url()."v2includes/style/sickleave.style.css";
						$use_the_form = "sickleave";
						
						$sick_leave = "select 
											distinct(grp_id),
											tb1.f_name,
											tb1.daily_rate,
											tb1.Division_Desc,
											tb1.date_added,
											tb1.checkdate,
											tb1.leave_application_details,
											tb1.employee_id,
											tb1.DBM_Sub_Pap_Desc
										from
										(select 
											ce.grp_id,
											cll.leave_application_details, 
											e.employee_id, 
											e.f_name, 
											e.daily_rate, 
											d.Division_Desc, 
											ce.date_added,
											dsp.DBM_Sub_Pap_Desc,
											ce.checkdate from checkexact as ce 
											left join checkexact_approvals as cea 
												on ce.grp_id = cea.grp_id
											left join checkexact_leave_logs as cll 
												on ce.grp_id = cll.grp_id 
											left join employees as e 
												on ce.employee_id = e.employee_id 
											left join leaves as l 
												on ce.leave_id = l.leave_id 
											left join positions as p 
												on e.position_id = p.position_id 
											left join Division as d 
												on e.Division_id = d.Division_Id 
											left join DBM_Sub_Pap as dsp 
												on e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
										where ce.grp_id = '{$grp_id}') as tb1";
							
						$sl = $this->admin_model->array_utf8_encode_recursive($this->Globalproc->__getdata($sick_leave));
						
						$theinfo['sl_details']	= $sl[0]->leave_application_details;
						$theinfo['fname']		= $sl[0]->f_name;
						$theinfo['signature']	= base_url()."assets/esignatures/".$this->Globalproc->gdtf("employees",['employee_id'=>$sl[0]->employee_id],['e_signature'])[0]->e_signature;
						$theinfo['rate']		= $sl[0]->daily_rate;
						$theinfo['off_div']		= /*$sl[0]->DBM_Sub_Pap_Desc." - ".*/$sl[0]->Division_Desc;
						$theinfo['dateadded']	= $sl[0]->date_added;
						$theinfo['numofdays']	= count($sl);
						$theinfo['checkdates']  = "";
						$theinfo['reasons']		= $this->Globalproc->gdtf("checkexact",["grp_id"=>$grp_id],['reasons'])[0]->reasons;
						
						$browsed_emp_id 		= $sl[0]->employee_id;
						foreach($sl as $s) {
							$theinfo['checkdates'] .= $s->checkdate . " - ";
						}
						// getcredrec($exactid,$empid,$getwhat)
						/*
						$theinfo['rem_vl']       = $this->Globalproc->return_remaining( "VL", $sl[0]->employee_id );
						$theinfo['rem_sl']       = $this->Globalproc->return_remaining( "SL", $sl[0]->employee_id );
						$theinfo['total']		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];
						*/
					
						$theinfo['rem_vl']       = $this->Globalproc->getcredrec($grp_id,$sl[0]->employee_id,"vlrec");
						$theinfo['rem_sl']       = $this->Globalproc->getcredrec($grp_id,$sl[0]->employee_id,"slrec");
						$theinfo['total']		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];
						
					//	$theinfo['rem_vl']       = $this->Globalproc->getcredrec($grp_id,$vl[0]->employee_id,"vlrec");
					//	$theinfo['rem_sl']       = $this->Globalproc->getcredrec($grp_id,$vl[0]->employee_id,"slrec");
					//	$theinfo['total']		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];
						
						$theinfo['rem_cc']       = $this->Globalproc->getcredrec($grp_id,$sl[0]->employee_id,"cocrec")/60/60;
						
						$theinfo['theleave'] 	 = $theinfo["numofdays"];
						$theinfo['theless'] 	 = $theinfo['rem_vl'] - $theinfo["numofdays"];
						$theinfo['lt']			 = "sl";
						
						$theinfo['tot_rem']		 = $theinfo['rem_vl'] - $theinfo["numofdays"];
						
						break;
					
					case "2": // vacation
						$data['headscripts']['style'][] = base_url()."v2includes/style/generic.style.css";
						$use_the_form = "generic";
						
						$vl_sql = "select 
										distinct(grp_id),
										tb1.f_name,
										tb1.daily_rate,
										tb1.Division_Desc,
										tb1.date_added,
										tb1.checkdate,
										tb1.leave_application_details,
										tb1.employee_id,
										tb1.DBM_Sub_Pap_Desc
									from
									(select 
										ce.grp_id,
										cll.leave_application_details, 
										e.employee_id, 
										e.f_name, 
										e.daily_rate, 
										d.Division_Desc, 
										ce.date_added,
										dsp.DBM_Sub_Pap_Desc,
										ce.checkdate from checkexact as ce 
										left join checkexact_approvals as cea 
											on ce.grp_id = cea.grp_id
										left join checkexact_leave_logs as cll 
											on ce.grp_id = cll.grp_id 
										left join employees as e 
											on ce.employee_id = e.employee_id 
										left join leaves as l 
											on ce.leave_id = l.leave_id 
										left join positions as p 
											on e.position_id = p.position_id 
										left join Division as d 
											on e.Division_id = d.Division_Id
										left join DBM_Sub_Pap as dsp 
												on e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
									where ce.grp_id = '{$grp_id}') as tb1
									";
						
						$vl = $this->admin_model->array_utf8_encode_recursive($this->Globalproc->__getdata($vl_sql));
						
						$loc 				 = Array();
						$loc["typeofleave"]  = "Vacation Leave";
						$loc["vl_det"]		 = $vl[0]->leave_application_details;
							// if abroad 
							if ($vl[0]->leave_application_details == 2) {
								$loc['abroad_dets'] = $this->Globalproc->gdtf("checkexact",["grp_id"=>$grp_id],['reasons'])[0]->reasons;
							}
								
						$loc["signature"]    = $this->Globalproc->gdtf("employees",["employee_id"=>$vl[0]->employee_id],["e_signature"])[0]->e_signature;
						$loc["fullname"]     = $vl[0]->f_name;
						$loc["month_sal"]    = $vl[0]->daily_rate;
						$loc["off_div"]      = $vl[0]->Division_Desc;
						$loc["dateoffiling"] = $vl[0]->date_added;
						$loc["noofdays"]     = count($vl);
						$browsed_emp_id 	 = $vl[0]->employee_id;
						
						$inclusive_dates 		 = "";
						foreach($vl as $o) {
							$inclusive_dates 	.= $o->checkdate." - ";
						}
						
						$loc["inclusive_dates"]  = $inclusive_dates;
						
						array_push($theinfo,$loc);
						
						/*
						$theinfo['rem_vl']       = $this->Globalproc->return_remaining( "VL", $vl[0]->employee_id );
						$theinfo['rem_sl']       = $this->Globalproc->return_remaining( "SL", $vl[0]->employee_id );
						$theinfo['total']		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];
						*/
						$theinfo['rem_vl']       = $this->Globalproc->getcredrec($grp_id,$vl[0]->employee_id,"vlrec");
						$theinfo['rem_sl']       = $this->Globalproc->getcredrec($grp_id,$vl[0]->employee_id,"slrec");
						$theinfo['total']		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];
						
						$theinfo['rem_cc']       = $this->Globalproc->getcredrec($grp_id,$vl[0]->employee_id,"cocrec")/60/60;
						
						$theinfo['theleave'] 	 = $loc["noofdays"];
						$theinfo['theless'] 	 = $theinfo['rem_vl'] - $loc["noofdays"];
						$theinfo['lt']			 = "vl";
						
						$theinfo['tot_rem']		 = $theinfo['rem_cc']; // - $loc["noofdays"];
						
						array_push($theinfo,$loc);
					
						break;
					
					case "4": // spl	
						$data['headscripts']['style'][] = base_url()."v2includes/style/spl.style.css";
						$use_the_form = "spl";
						
						$sql = "select 
									distinct(grp_id),
									tb1.*,
									e.firstname,
									e.l_name,
									e.m_name,
									d.Division_Desc,
									p.position_name,
									dsp.DBM_Sub_Pap_Desc
								from 
								(select 
									ce.grp_id,
									ce.employee_id,
									ce.type_mode,
									ce.type_mode details,
									ce.checkdate,
									ce.date_added,
									cll.spl_personal_milestone,
									cll.spl_filial_obligations,
									cll.spl_personal_transaction,
									cll.spl_parental_obligations,
									cll.spl_domestic_emergencies,
									cll.spl_calamity_acc
								 from checkexact as ce
								JOIN checkexact_leave_logs as cll
								on ce.grp_id = cll.grp_id 
								where ce.grp_id = '{$grp_id}') as tb1
									left join employees as e 
										on tb1.employee_id = e.employee_id
									left join Division as d
										on e.Division_id = d.Division_Id
									left join positions as p
										on e.position_id = p.position_id
									left join DBM_Sub_Pap as dsp 
										on e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
								 ";
						
						// $theinfo // array
						$od = $this->admin_model->array_utf8_encode_recursive($this->Globalproc->__getdata($sql));
						
						$theinfo['lname']     	  = $od[0]->l_name;
						$theinfo['fname']     	  = $od[0]->firstname;
						$theinfo['mname']  	  	  = $od[0]->m_name;
						
						$theinfo['officediv'] 	  = $od[0]->Division_Desc;
						$theinfo['position']  	  = $od[0]->position_name;
						$theinfo['dateoffiling']  = $od[0]->date_added;
						
						$theinfo['spl_items']	  = [];
						$theinfo['dates']		  = "";
						$theinfo['numofdays']	  = count($od);
						
						$browsed_emp_id 	 = $od[0]->employee_id;
						$theinfo['signature']	  = base_url()."assets/esignatures/".$this->Globalproc->gdtf("employees",
																			['employee_id'=>$od[0]->employee_id],
																			['e_signature'])[0]->e_signature;
											
						foreach($od as $o) {
							$theinfo['spl_items']['spl_personal_milestone']    = $o->spl_personal_milestone;
							$theinfo['spl_items']['spl_filial_obligations']    = $o->spl_filial_obligations;
							$theinfo['spl_items']['spl_personal_transaction']  = $o->spl_personal_transaction;
							$theinfo['spl_items']['spl_parental_obligations']  = $o->spl_parental_obligations;
							$theinfo['spl_items']['spl_domestic_emergencies']  = $o->spl_domestic_emergencies;
							$theinfo['spl_items']['spl_calamity_acc']  		   = $o->spl_calamity_acc;
							
							$theinfo['dates'] .= "<strong>".$o->checkdate."</strong> - ";
						}
						
						/*
						// approvals 
							$approvals = $this->Globalproc->gdtf("checkexact_approvals",['grp_id'=>$grp_id], 
																['division_chief_id','division_chief_is_approved','division_chief_remarks','division_date',
																 'leave_authorized_official_id','leave_authorized_is_approved','leave_authotrized_remarks','leave_authorized_date']
																);
						// end
						
						// recommending approval
							$recommending 							= $this->Globalproc->gdtf("employees",['employee_id'=>$approvals[0]->division_chief_id],['f_name','e_signature']);
							$theinfo['recommending']['name'] 		= $recommending[0]->f_name;
							$theinfo['recommending']['signature'] 	= base_url()."assets/esignatures/".$recommending[0]->e_signature;
							$theinfo['recommending']['date'] 		= $approvals[0]->division_date;
							$theinfo['recommending']['isapproved'] 	= $approvals[0]->division_chief_is_approved;
							$theinfo['recommending']['remarks'] 	= $approvals[0]->division_chief_remarks;
						// end 
						
						// last approving official 
							$last 									= $this->Globalproc->gdtf("employees",
																							  ["employee_id"=>$approvals[0]->leave_authorized_official_id],
																							  ["f_name","e_signature"]
																							  );
							$theinfo['last']['name']				= $last[0]->f_name;
							$theinfo['last']['signature']			= base_url()."assets/esignatures/".$last[0]->e_signature;
							$theinfo['last']['date']				= $approvals[0]->leave_authorized_date;
							$theinfo['last']['isapproved']			= $approvals[0]->leave_authorized_is_approved;
							$theinfo['last']['remarks']				= $approvals[0]->leave_authotrized_remarks;
						// end 
						*/
						
						
						
						// remaining spl 
							$theinfo['remaining']					= $this->Globalproc->get_spl_count($od[0]->employee_id);
						// end 
						
						break;
						
					default: // other 
						$data['headscripts']['style'][] = base_url()."v2includes/style/generic.style.css";
						$use_the_form = "generic";
						
						$sql = "select 
									distinct(tb1.exact_id),
									tb1.leave_name,
									tb1.f_name,
									tb1.daily_rate,
									tb1.Division_Desc,
									tb1.date_added,
									tb1.checkdate,
									tb1.exact_id,
									tb1.grp_id,
									tb1.employee_id,
									tb1.DBM_Sub_Pap_Desc
								from
								(select 
									l.leave_name, 
									e.employee_id,
									e.f_name, 
									e.daily_rate, 
									d.Division_Desc, 
									ce.date_added, 
									ce.checkdate, 
									ce.exact_id, 
									ce.grp_id,
									dsp.DBM_Sub_Pap_Desc
								from checkexact_leave_logs as cll 
									left JOIN checkexact as ce 
										on cll.grp_id = ce.grp_id 
									left JOIN leaves as l 
										on ce.leave_id = l.leave_id 
									left JOIN employees as e 
										on e.employee_id = ce.employee_id 
									left JOIN Division as d 
										on e.Division_id = d.Division_Id 
									left join DBM_Sub_Pap as dsp 
										on e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
								where ce.grp_id = '{$grp_id}')
									 as tb1";
								
						$od = $this->admin_model->array_utf8_encode_recursive($this->Globalproc->__getdata($sql));
												
						$words 	  = explode(" ",$od[0]->leave_name);
						$lastword = "";
						
						if ( $words[ count($words)-1 ] != "Leave" || $words[ count($words)-1 ] != "Leave" ) {
							$lastword = " Leave";
						}
						
						$loc 				 = Array();
						$loc["typeofleave"]  = $od[0]->leave_name.$lastword;
						$loc["signature"]    = $this->Globalproc->gdtf("employees",["employee_id"=>$od[0]->employee_id],["e_signature"])[0]->e_signature;
						$loc["fullname"]     = $od[0]->f_name;
						$loc["month_sal"]    = $od[0]->daily_rate;
						$loc["off_div"]      = $od[0]->Division_Desc;
						$loc["dateoffiling"] = $od[0]->date_added;
						$loc["noofdays"]     = count($od);
						
						$browsed_emp_id 	 = $od[0]->employee_id;
						$inclusive_dates 		 = "";
						foreach($od as $o) {
							$inclusive_dates 	.= $o->checkdate." - ";
						}
						
						$loc["inclusive_dates"]  = $inclusive_dates;
						
						array_push($theinfo,$loc);
						
						/*
						$theinfo['rem_vl']       = $this->Globalproc->return_remaining( "VL", $od[0]->employee_id );
						$theinfo['rem_sl']       = $this->Globalproc->return_remaining( "SL", $od[0]->employee_id );
						$theinfo['total']		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];
						*/
						$theinfo['rem_vl']       = $this->Globalproc->getcredrec($grp_id,$od[0]->employee_id,"vlrec");
						$theinfo['rem_sl']       = $this->Globalproc->getcredrec($grp_id,$od[0]->employee_id,"slrec");
						$theinfo['total']		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];

				
						break;
				}
			} else {
				// leave_id = 0, meaning ps, ams, paf, ot, cto
				$data['headscripts']['style'][] = base_url()."v2includes/style/generic.style.css";
				
				$use_the_form = "generic";
				
				$sql = "select *, ce.date_added as da from checkexact as ce
						left JOIN employees as e on 
						ce.employee_id = e.employee_id
						left join Division as d on 
							e.Division_id = d.Division_Id
						left join DBM_Sub_Pap as dsp 
							on e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
						where ce.grp_id = '{$grp_id}'";
				$cto = $this->admin_model->array_utf8_encode_recursive($this->Globalproc->__getdata($sql));
			
				$loc 				 = Array();
				$loc["typeofleave"]  = "CTO (compensatory time-off)";
					
				$loc["signature"]    = $this->Globalproc->gdtf("employees",["employee_id"=>$cto[0]->employee_id],["e_signature"])[0]->e_signature;
				$loc["fullname"]     = $cto[0]->f_name;
				$loc["month_sal"]    = $cto[0]->daily_rate;
				$loc["off_div"]      = $cto[0]->Division_Desc;
				$loc["dateoffiling"] = $cto[0]->da;
				
				$browsed_emp_id 	 = $cto[0]->employee_id;
				$amin  = "00:00";
				$amout = "00:00";
				// --------- //
				$pmin  = $cto[0]->time_in;
				$pmout = $cto[0]->time_out;
				
				// 
					$time1 = null;
					$time2 = null;
				// 
				// echo $cto[0]->checkdate;
				if( date("A",strtotime($cto[0]->time_in)) == "AM" ) {
					$amin  = $cto[0]->time_in;
					if ( date("A",strtotime($cto[0]->time_out)) == "AM" ) {
						$amout = $cto[0]->time_out;
						
						// afternoon
							$pmin  = 0;
							$pmout = 0;
						// end afternoon
					} else if ( date("A",strtotime($cto[0]->time_out)) == "PM" ) {
						$amout = "12:00 PM";
							
						// pm 
							$pmin  = "1:00 PM";
							// $pmout = default;
						// end 
					}
										
				} /* else if ( date("A",strtotime($cto[0]->time_in)) == "PM" ) {
					$pmin  = $cto[0]->time_in;
					$pmout = $cto[0]->time_out;
				} */
				
				$time1 					 = strtotime($amin) - strtotime($amout);
				$time2 		     		 = strtotime($pmin) - strtotime($pmout);
				$difference 			 = round(abs($time2 + $time1) / 3600,2);
				
				$loc["noofdays"]     	 = $difference . " Hour";
				$loc['noofdays']	 	.= ( $difference == 1 )?"":"s";
				
				$inclusive_dates 	 	 = $cto[0]->time_in . " - " . $cto[0]->time_out;
					
				$loc["inclusive_dates"]  = date("F d, Y",strtotime($cto[0]->checkdate)) . " at " .$inclusive_dates;
				
				if (count($cto)>1) {
					$loc["noofdays"] 			= 8*count($cto)." hours ";
					$loc["inclusive_dates"]		= "";
					
					foreach($cto as $c) {
						$loc['inclusive_dates'] .= date("l d, Y", strtotime($c->checkdate)) . " - ";
					}
					
					$loc["inclusive_dates"] .= "<br/> from: ".$inclusive_dates;
				}
				
				array_push($theinfo,$loc);
				
				/*
				$theinfo['rem_vl']       = $this->Globalproc->return_remaining( "VL", $cto[0]->employee_id );
				$theinfo['rem_sl']       = $this->Globalproc->return_remaining( "SL", $cto[0]->employee_id );
				*/
			
				$theinfo['rem_vl']       = $this->Globalproc->getcredrec($grp_id,$cto[0]->employee_id,"vlrec");
				$theinfo['rem_sl']       = $this->Globalproc->getcredrec($grp_id,$cto[0]->employee_id,"slrec");
				$theinfo['rem_cc']       = $this->Globalproc->getcredrec($grp_id,$cto[0]->employee_id,"cocrec")/60/60;
				
				$theinfo['total'] 		 = $theinfo['rem_vl'] + $theinfo['rem_sl'];
				
				$theinfo['theleave'] 	 = $difference;
				$theinfo['theless'] 	 = $theinfo['rem_cc'] - $difference;
				$theinfo['lt']			 = "coc";
				
				$theinfo['tot_rem']		 = $theinfo['rem_cc'] - $difference;
				
				array_push($theinfo,$loc);
			}
			
			// get the approval status
				$approvals 	 		  = $this->Globalproc->gdtf("checkexact_approvals",["grp_id"=>$grp_id],"*");
				
			// 
				$data['isapp_disapp'] = $this->Globalproc->gdtf("checkexact",["grp_id"=>$grp_id],["is_approved"]);
				
				// HR 
					$hr = $this->Globalproc->gdtf("employees",['employee_id'=>$approvals[0]->hrmd_approved_id],["f_name","e_signature"]);
					
					if (count($hr)==0) {	
						$hr = $this->Globalproc->gdtf("employees",
													  ['Division_id'=>19, // the HR unit's division ID 
													   "conn" => "and",
													   'is_head' => 1],	  // head of the HR, or someone set as head of HR
													  ['f_name','e_signature']);
					}
					
					if (count($hr)==0) {
						// hard code the HR signatory // base_url()."/assets/esignatures/ 
						$hr[0] = (Object) array("f_name"=>"Cecilia Trino","e_signature"=>"95e6b0b5a4ce93e853b6cfbe26cfa6ff.png");
					}
					
					$theinfo['hr']['name']					= $hr[0]->f_name;
					$theinfo['hr']['signature']				= base_url()."assets/esignatures/".$hr[0]->e_signature;
					$data['hr_date']						= date("l, F d, Y", strtotime($approvals[0]->hrmd_date));
				// end 
						
				// division head 
					$div_details   = $this->Globalproc->gdtf("employees",
															["employee_id"=>$approvals[0]->division_chief_id],
															["f_name","e_signature","position_id"]);
					$div_head_name = $div_details[0]->f_name;
					$div_head_sig  = base_url()."assets/esignatures/".$div_details[0]->e_signature;
					$div_desig 	   = $this->Globalproc->gdtf("positions",
															["position_id"=>$div_details[0]->position_id],
															["position_name"])[0]->position_name;
					$div_approved  		  = $approvals[0]->division_chief_is_approved;
					$data['remarks_div']  = $approvals[0]->division_chief_remarks;
					$data['div_date'] 	  = $approvals[0]->division_date;
				// end division 
			
				// ==================================================
			
				// last approving official
					$last_head_details = $this->Globalproc->gdtf("employees",["employee_id"=>$approvals[0]->leave_authorized_official_id],
																["f_name","e_signature","position_id"]);
					$last_head_name    = $last_head_details[0]->f_name;
					$last_head_sig     = base_url()."assets/esignatures/".$last_head_details[0]->e_signature;
					$div_last_desig    = $this->Globalproc->gdtf("positions",
																["position_id"=>$last_head_details[0]->position_id],
																["position_name"])[0]->position_name;
					$last_approved 	   = $approvals[0]->leave_authorized_is_approved;
					$data['remarks']   = $approvals[0]->leave_authotrized_remarks;
					$data['last_date'] = $approvals[0]->leave_authorized_date;
				// end last approving official
			// end
			
			// the browsed employee id 
				$data['browsed_emp_id'] 	 = $browsed_emp_id;
			// end 
			
			$theinfo['specialcode']			 = $grp_id."-".$browsed_emp_id; // substr(md5($browsed_emp_id),0,7);
			$data['information']			 = $theinfo;
			
			/*
			$data['approvals']				 = Array(["last"		=> Array($last_head_name,$last_head_sig,$div_last_desig,$last_approved,$approvals[0]->leave_authorized_official_id)]);
			
			if ($approvals[0]->division_chief_id == $browsed_emp_id) {
				array_push($data['approvals'],["division" => Array($div_head_name,$div_head_sig,$div_desig,$div_approved,$approvals[0]->division_chief_id)]);
			}
			
			*/
			$data['approvals']				 = Array(
													["division" => Array($div_head_name,$div_head_sig,$div_desig,$div_approved,$approvals[0]->division_chief_id)],
													["last"		=> Array($last_head_name,$last_head_sig,$div_last_desig,$last_approved,$approvals[0]->leave_authorized_official_id)]
												);
			
			
			$data['title']		  			 = "Form application";
	
			// get the display of signature settings 
				$data['sig_data'] = $this->Globalproc->getsettings("signature");
			// end 
		
			$data['main_content'] 			 = "v2views/forms/".$use_the_form;
			
			$this->load->view('hrmis/admin_view',$data);
		}
		
		function onehour() {
			$start_time  = new DateTime("09:00 AM");
			$end_time    = new DateTime("10:00 AM");
			$interval    = $start_time->diff($end_time);
			$cto_hours 	 = $interval->format('%h');
			$cto_mins    = $interval->format('%i');
		
			echo $cto_hours; 
				echo "<br/>";
			echo $cto_mins;
			
		}
		
		function dayminusone() {
			$date_raw = "07/06/2018";
			echo date("n/j/Y",strtotime($date_raw))."<br/>";
			$yesterday = date('n/j/Y', strtotime('+2 day', strtotime($date_raw)));
			echo $yesterday."|".date("D",strtotime($yesterday));
		}

	}

