<?php 
	
	class Emailtemplate extends CI_Model {
		
		function leavetemplate($details) {
			$url = base_url();
			$template = "<!DOCTYPE html>
						<html>
						<body style='font-family:arial; margin:0px; padding:0px;'>
							<div style='width: 100%;
										margin: 8px auto;
										border: 1px solid #e0e0e0;
										box-shadow: 0px 3px 3px #e2e2e2;'>
								<table style='margin: auto; min-width: 50%; border-collapse: collapse;'>
									<tr style='background: #e7e7e7;border-bottom: 1px solid #d5d5d5;'>
										<td colspan='2' style='text-align: center; padding: 25px;'>
											<div class='login-logo'>
												<a href='{$url}'><img style='width:100px;' src='{$url}assets/images/minda_logo.png'>  </a>
												<p style='margin: 0px;font-size: 27px;'> MinDa </p>
											</div>
										</td>
									</tr>
									<tr style='border-bottom: 1px solid #ccc; background: #e6e6e6;'>
										<td style='text-align: right;'> 
											<div style='background-image:url(https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/svgs/fi-clipboard-pencil.svg);
														height: 23px;
														width: 30px;
														float: right;
														background-repeat: no-repeat;
														background-size: 22px;'>
											</div> 
										</td>
										<td style='text-align: left ;font-size: 23px;padding: 20px 0px;'> Needs Approval </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; padding: 9px 10px; color: #656363;'> type : </td>
										<td> {$details['type']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363;'> specific: </td>
										<td> {$details['specific']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363;'> Name: </td>
										<td> {$details['name']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363;'> Position: </td>
										<td> {$details['position']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363;'> Office: </td>
										<td> {$details['office']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363;'> Date Filing: </td>
										<td> {$details['dateoffiling']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363;'> Inclusive Dates: </td>
										<td> {$details['inc_dates']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right;padding: 9px 10px; font-size: 13px; color: #656363;'> Number of Working days Applied: </td>
										<td> {$details['no_days_applied']} </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363;'> View the form: </td>
										<td> <a href='{$details['link']}'>View link</a> </td>
									</tr>
									<tr style='border-bottom: 1px solid #e0e0e0;'>
										<td style='text-align:right; padding: 9px 10px; font-size: 13px; color: #656363; width: 40%;'> Approved By: </td>
										<td> {$details['approvedby']} </td>
									</tr>
								</table>
									<div style='overflow: hidden;
												text-align: center;
												background: #f7f7f7;
												margin: auto;
												padding: 20px 0px;'>
											<a href='".base_url()."action/form/approve/{$details['exactid']}/{$details['approvingid']}/{$details['token']}/{$details['isfinal']}' style='text-decoration: none;'> <p style='padding: 15px 10px;
																					  background: #7decdd; 
																					   text-align: center; 
																					   width: 25%; 
																					   display: inline-block;
																					   color: #404040;
																					   font-size: 13px;
																					  border-radius: 30px;
																						border: 1px solid #5ccebe;
																						box-shadow: 0px 2px 2px #c7c7c7;'> APPROVE </p> </a> 

											<a href='".base_url()."action/form/decline/{$details['exactid']}/{$details['approvingid']}/{$details['token']}/{$details['isfinal']}' style='text-decoration: none;'><p style='padding: 15px 10px; 
																					   background: #d87979; 
																					   text-align: center; 
																					   width: 25%; 
																					   display: inline-block;
																					   color: #404040;
																					   font-size: 13px;
																					   border-radius: 30px;
																						border: 1px solid #d03e3e;
																						box-shadow: 0px 2px 2px #c7c7c7;'> DISAPPROVE </p> </a>
															</div>
														</div>
														<p style='margin: 0px auto;
																	text-align: center;
																	font-size: 11px;
																	padding: 11px 0px;
																	color: #9c9898;'>
																MinDa HRIS
															</p>
													</body>

													</html>	";
			return $template;
		}
		
		function new_leavetemplate($details, $action_details) {
//  http://office.minda.gov.ph:9003/action/form/approve/6507/349/0a090174382/true 	
//			var_dump($action_details);
			$isfinal = ($action_details['isfinal'] == true)?"true":"false";
			$url 	= base_url()."/action/form/";
			$url_1  = base_url();
			$template = "<!DOCTYPE html>
							<html>
							<body style='font-family:arial; margin:0px; padding: 36px 0px; background: #d1d1d1;'>
								<div style='width: 100%;
											margin: 8px auto;
											border: 1px solid #e0e0e0;
											box-shadow: 0px 3px 3px #e2e2e2;
											background:#fff;'>
									<table style='margin: auto; min-width: 50%; border-collapse: collapse;'>
									<tr style='background: #e7e7e7;border-bottom: 1px solid #d5d5d5;'>
										<td colspan='2' style='text-align: center; padding: 25px;'>
											<div class='login-logo'>
												<a href='{$url_1}'><img style='width:100px;' src='{$url_1}assets/images/minda_logo.png'>  </a>
												<p style='margin: 0px;font-size: 27px;'> MinDa </p>
											</div>
										</td>
									</tr>
									<tr style='border-bottom: 1px solid #ccc; background: #ffffff;'>
										<td style='text-align: left ;font-size: 23px;padding: 20px 16px;' colspan='2'> Needs Approval </td>
									</tr>
									";

									for($i = 0; $i <= count($details)-1; $i++) {
										$template .= "<tr style='border-bottom: 1px solid #e0e0e0;'>
														<td style='text-align: right; width: 32%; padding: 9px 10px; font-size: 13px; padding: 9px 10px; color: #656363;'> 
															{$details[$i][0]}
														</td>
														<td>"; 
												
												if (is_array( $details[$i][1] )) {
													for ($a = 0; $a <= count($details[$i][1])-1; $a++) {
														$template .= $details[$i][1][$a];
														$template .= ($a == count($details[$i][1])-1)?" " :" - ";
													}
												} else {
													$template .= $details[$i][1];
												}
										
										$template .="	</td>
													</tr>";
									}
		
			$template .= "
								</table>
									<div style='overflow: hidden;
												text-align: center;
												background: #f7f7f7;
												margin: auto;
												padding: 20px 0px;'>
										<a href='{$url}/approve/{$action_details['grp_id']}/{$action_details['approving_person_id']}/{$action_details['token']}/{$isfinal}' style='text-decoration: none;'> <p style='padding: 15px 10px;
															   background: #7decdd; 
															   text-align: center; 
															   width: 25%; 
															   display: inline-block;
															   color: #404040;
															   font-size: 13px;
															  border-radius: 30px;
																border: 1px solid #5ccebe;
																box-shadow: 0px 2px 2px #c7c7c7;'> APPROVE </p> </a> 

										<a href='{$url}/decline/{$action_details['grp_id']}/{$action_details['approving_person_id']}/{$action_details['token']}/{$isfinal}' style='text-decoration: none;'> <p style='padding: 15px 10px; 
															   background: #d87979; 
															   text-align: center; 
															   width: 25%; 
															   display: inline-block;
															   color: #404040;
															   font-size: 13px;
															   border-radius: 30px;
																border: 1px solid #d03e3e;
																box-shadow: 0px 2px 2px #c7c7c7;'> DISAPPROVE </p> </a>
									</div>
								</div>
								<p style='margin: 0px auto;
											text-align: center;
											font-size: 11px;
											padding: 11px 0px;
											color: #9c9898;'>
										MinDa HRIS
									</p>
							</body>

							</html>	";
			return $template;
		}
		
		function ot_accom_template($details, $action_details = false) {
			$url_1  = base_url();
			$template = "<!DOCTYPE html>
							<html>
								<body style='font-family:arial; margin:0px; padding: 36px 0px; background: #d1d1d1;'>
									<div style='width: 100%;
												margin: 8px auto;
												border: 1px solid #e0e0e0;
												box-shadow: 0px 3px 3px #e2e2e2;
												background:#fff;'>
										
										<table style='margin: auto; width: 50%; border-collapse: collapse;'>
											<tr style='background: #e7e7e7;border-bottom: 1px solid #d5d5d5;'>
												<td colspan='2' style='text-align: center; padding: 25px;'>
													<div class='login-logo'>
														<a href='{$url_1}'><img style='width:100px;' src='{$url_1}assets/images/minda_logo.png'>  </a>
														<p style='margin: 0px;font-size: 27px;'> MinDa </p>
													</div>
												</td>
											</tr>
											<tr style='border-bottom: 1px solid #ccc; background: #ffffff;'>
												<td style='text-align: left ;font-size: 23px;padding: 20px 16px;' colspan='2'> Needs Approval </td>
											</tr>";
											
									foreach($details as $key => $val) {
										$template .= "<tr style='border-bottom: 1px solid #e0e0e0;'>";
											$template .= "<td style='text-align: right; width: 32%; padding: 9px 10px; font-size: 13px; padding: 9px 10px; color: #656363;'> ";
												$template .= $key;
											$template .= "</td>";
											$template .= "<td>";
												$template .= $val;
											$template .= "</td>";
										$template .= "</tr>";
									}
			$base_url =	base_url(); // approval($ot_exact = '', $approving_id = '', $ot_accom_id = '')
			$template .= "			    </table>
										
										<div style='overflow:hidden;text-align:center;background:#f7f7f7;margin:auto;padding:20px 0px'>
											<a href='{$base_url}approval/accomplishment/{$action_details['ot_exact']}/{$action_details['approving_id']}/{$action_details['ot_accom_id']}'>
												<p style='padding:15px 10px;background:#7decdd;text-align:center;width:25%;display:inline-block;color:#404040;font-size:13px;border-radius:30px;border:1px solid #5ccebe'> APPROVE </p>
											</a>
										</div>
									</div>
								</body>
							</html>";
			return $template; // <p style='padding:15px 10px;background:#d87979;text-align:center;width:25%;display:inline-block;color:#404040;font-size:13px;border-radius:30px;border:1px solid #d03e3e'> DISAPPROVE </p>
		}
		
		function dbm_alldtr($division,$details) {
			$template = "	<html>
								<body style='font-family:arial; margin:0px; padding: 36px 0px; background: #d1d1d1;'>
									<div style='width: 100%;
												margin: 8px auto;
												border: 1px solid #e0e0e0;
												box-shadow: 0px 3px 3px #e2e2e2;
												background:#fff;'>
										<table style='margin: auto; width: 50%; border-collapse: collapse;'>
											<tr style='border-bottom: 1px solid #ccc; background: #ffffff;'>
												<td style='text-align: left ;font-size: 23px;padding: 20px 16px;' colspan='2'> {$division} : DTR needs approval</td>
											</tr>";
											/*
											<tr style='border-bottom: 1px solid #e0e0e0;'>
												<td style='text-align: right; width: 5%; font-size: 13px; padding: 10px; color: #656363; vertical-align: top;'> 
													<p style='margin:0px;'> 1. </p>
												</td>
												<td style='vertical-align: top; padding: 10px;'>
													<p style='margin:0px;'> Alvin Merto </p>
													<span style='font-size: 14px; color: green;'> Approved </span>
												</td>
											</tr>
											*/
											
											
											
			$template .=				"</table>
										<div style='overflow:hidden;text-align:center;background:#f7f7f7;margin:auto;padding:20px 0px'>
											<p style='padding:15px 10px;background:#7decdd;text-align:center;width:25%;display:inline-block;color:#404040;font-size:13px;border-radius:30px;border:1px solid #5ccebe'> View Division
											</p>
										</div>
									</div>
								</body>
							</html>";
		}
	}

