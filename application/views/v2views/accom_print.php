 < 
 <div class="content-wrapper" style='padding-top:0px;'>
    <!-- Content Header (Page header) -->
    <!--section class="content-header"-->
      <!--ol class="breadcrumb">
        <li class="active"><img style="margin-top:-14px;" src="<?php // echo base_url();?>assets/images/minda/rsz_1minda_logo_text.png" /></li>
      </ol-->
    <!--/section-->

    <!-- Main content -->
    <section class="content" style="padding-top: 0px;">
		<div class="row">
			<div style='padding:6px 20px 20px 20px;'>
				<?php if (!isset($isviewing)): ?>
				<?php $curr_link = base_url().'my/accomplishments/'; ?>
				<h3> <a href='<?php echo $curr_link; ?>' style='font-size:14px; position: relative;top: -3px;'> Accomplishment Report </a> &nbsp; <i class="fa fa-angle-right"></i> &nbsp; Print accomplishment report </h3>
				<hr style='margin: -1px 0px;'/>
				<div class='option_pallete'> <!-- option_pallete -->
					<p> Choose DTR coverage date: </p>
					<form method='POST'>
						<div class='selectbtns'>
							<input type='text' id='range_date_print_accom' name='range_date_print_accom'/> 
							<input type='hidden' id='from_' name='from_' value='<?php if(isset($from_)){ echo $from_; } ?>'/>
							<input type='hidden' id='to_' name='to_' value='<?php if(isset($to_)){ echo $to_; } ?>'/>
							<input type='text' name='signatory' id='signatory' placeholder='Name of the signatory' style='margin-top: 15px;' value='<?php if (isset($_POST['signatory'])) { echo $_POST['signatory'];  } ?>'/>
							<input type='submit' value='Show Reports' class='btn btn-primary' name='print_accom' style='float: right; margin-top: 10px;'/> 
						</div>
				<?php endif; ?>
						<div class='utilbtns'>
							<?php //if (isset($accomplishments)): ?> 
								<?php 
									if (isset($notloggedin) && $notloggedin == true) {
										echo "<script src='" . base_url() . "assets_new/plugins/jQuery/jquery-2.2.3.min.js'></script>";
									}
								?>
									<script>
										jQuery(document).ready(function() {
											var divToPrint=document.getElementById('the_accomplishment_report');
											
											$("#print_this").on("click", function() {
											  var newWin=window.open('','Print-Window');
												  newWin.document.open();
												  newWin.document.write("<html> <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'/> <link rel='stylesheet' href='<?php echo base_url(); ?>v2includes/style/accomplishments.style.css'/> <body onload='window.print()' style='font-family: \"Source Sans Pro:\",\"Helvetica Neue\",Helvetica,Arial,sans-serif;'>"+divToPrint.innerHTML+"</body></html>");
												/*
													newWin.document.write('<!DOCTYPE html>\n'+
																		'<html>\n' +
																		'<head>\n' +
																		'<meta charset="utf-8" />\n' +
																		'<style> @media print{@page {size: Legal portrait; margin-top:-100px !important; } .accom_me_mid { width:100%; }} </style>'  +
																		'<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"/>' +
																		'<link rel="stylesheet" href="<?php echo base_url(); ?>v2includes/style/accomplishments.style.css"/>' +
																		'</head>\n'+
																		'<body onload="window.print()" style="width: 1405px; height: 816px; margin:0px !important; font-family:calibri;">\n' + divToPrint.innerHTML +  '\n</body>\n</html>');
												 */ 
												  newWin.document.close();
											})
											
											$("#savetoword").on("click", function(event){
												jQuery("#the_accomplishment_report").wordExport("<?php echo $personal_info[0]->f_name."_Accomplishment Report"; ?>");
											});
										})
									</script>
									
								<div class="btn-group" style="float:right;">
									<?php if (isset($showshare)): ?>
										<a style='margin-left: 0px;' class='btn btn-primary' id='print_this'> <i class="fa fa-print"></i> Print </a> 
										<!--a class='btn btn-default'> Send to chief </a-->					
										<?php 
											if (isset($notloggedin)) {
												if ($notloggedin == false) {
													echo "<p class='btn btn-default' id='sharetoperson' href='javascript:void(0)'> <i class='fa fa-leanpub'></i> Share </p>";
												}
											} else {
												echo "<p class='btn btn-default' id='sharetoperson' href='javascript:void(0)'> <i class='fa fa-leanpub'></i> Share </p>";
											}
											
										?>
										<div class='sharewdiv'>
											<div>
												<h4> Share your accomplishment report <i class="fa fa-times" id='floatr'></i> </h4>
												<hr/>
												<p> Shareable link </p>
												<input type='text' value='<?php echo $link; ?>' id='sharelink'/>
												
												<hr/>
												<p> Share via email ( <small> use comma(,) to separate multiple email addresses </small> )</p>
												<input type='text' placeholder='alvinjay.merto@minda.gov.ph' id='emailadds'/>
												<input type='hidden' value='<?php echo $fname; ?>' id='fname'/>
												<br/><br/>
												<p class='btn btn-primary' id='sendlink' href='javascript:void(0)'> <i class="fa fa-leanpub"></i> send </p>
												<!--button class='btn btn-primary' id='sendlink'> send </button-->
												<hr/>
												<p id='statusp'> </p>
											</div>
										</div>
										<!--a class='btn btn-primary' id='attachtodtr' > Attach to DTR </a-->
									<?php endif; ?>
								</div>
							<?php //endif; ?>
						</div>
					</form>
				</div>
				<hr/>
				
				
					<?php if(isset($accomplishments)): ?>
					<!-- accomplishment report -->
						<div id='the_accomplishment_report'>
							<div class='accom_me_mid'>
								<img src='<?php echo base_url()."assets/images/d_accom_logo.jpg"; ?>' class='accom_div'/>
								<h4 style='text-align:center; font-family: calibri; font-size: 20px;'> <strong> DAILY ACCOMPLISHMENT REPORT </strong> </h4>
								
								<table style='width:100%; margin-top:30px; border-collapse: collapse;'>
									<tr>
										<td style='width: 11%;'>
											Name : 
										</td>
										<td>
											<?php echo $personal_info[0]->f_name; ?>
										</td>
									</tr>
									<tr>
										<td style='width: 11%;'>
											Position : 
										</td>
										<td>
											<?php echo $personal_info[0]->position_name; ?>
										</td>
									</tr>
								</table>
								<p style='margin:20px 0px;'> I certify to the following accomplishment(s) during my daily task: </p>
								
								<table style='width:100%;'>
									<tr>
										<th style='border:1px solid #333; padding:5px;text-align:center;'> S/N </th>
										<th style='border:1px solid #333; padding:5px; width: 25%;'> Date </th>
										<th style='border:1px solid #333; padding:5px;'> WORK ACCOMPLISHMENT </th>
									</tr>
									<?php $count = 1; 
											foreach($accomplishments as $ac) { 
											?>
										<tr>
											<td style='border:1px solid #333; padding:5px; text-align:center;'> <?php echo $count; ?> </td>
											<td style='border:1px solid #333; padding:5px; width: auto; text-align: center;'>
												<p style='margin:0px;'> <?php echo date("F d, Y",strtotime($ac->date)); ?> </p>
												<p style='margin:0px;'> ( <?php echo date("l", strtotime($ac->date)); ?> ) </p>
											</td>
											<td style='border:1px solid #333; padding:5px;'> <?php echo urldecode($ac->accomplishment); ?></td>
										</tr>
									<?php $count++; } ?>
								</table>
								
								<!--div style='width:100%;    overflow: hidden;'>
									<div style="overflow:hidden; border-top:1px solid #333;border-bottom:1px solid #333;">
										<div style=' padding:5px; float: left; width: 7%; border-left:1px solid #333;'> S/N </div>
										<div style='border-right:1px solid #333;border-left:1px solid #333; padding:5px; float: left; width: 25%;'> Date </div>
										<div style='padding:5px; width: 68%; float: left;border-right:1px solid #333;'> WORK ACCOMPLISHMENT </div>
									</div>
									<?php //$count = 1; 
											//foreach($accomplishments as $ac) { 
											?>
										<div style="overflow:hidden;border-bottom:1px solid #333; border-left:1px solid #333; border-right:1px solid #333;">
											<div style=' padding:5px; float: left; width: 7%;'> <?php echo $count; ?> </div>
											<div style='padding:5px; float: left; width: 25%; text-align: center; border-right:1px solid #333; border-left:1px solid #333;'>
												<p style='margin-bottom:0px;'> <?php echo date("F d, Y",strtotime($ac->date)); ?> </p>
												<p style='margin-bottom:0px;'> ( <?php echo date("l", strtotime($ac->date)); ?> ) </p>
											</div>
											<div style='padding:5px; float:left; width: 68%;'> <?php echo urldecode($ac->accomplishment); ?></div>
										</div>
									<?php // $count++; } ?>
								</div-->
								
								<div style='float:left; margin-top:80px; width:45%; text-align:center;'>
									<div style="background-image:url('<?php //echo base_url()."/assets/esignatures/".$personal_info[0]->e_signature; ?>'); 
												width: 100%;
												height: 112px;
												background-repeat: no-repeat;
												background-position: center;
												margin-bottom: -30px;">
										<img src='<?php // echo base_url()."/assets/esignatures/".$personal_info[0]->e_signature; ?>'/> 
									</div>
									<div style='border-top:1px solid #333;'>
										<p> Signature </p>
									</div>
								</div>
								<div style='float:right; margin-top:80px; width:45%; text-align:center;'>
									<div style="background-image:url('<?php //echo base_url()."/assets/esignatures/".$personal_info[0]->e_signature; ?>'); 
												width: 100%;
												height: 112px;
												background-repeat: no-repeat;
												background-position: center;
												margin-bottom: -30px;">
										<img src='<?php // echo base_url()."/assets/esignatures/".$personal_info[0]->e_signature; ?>'/> 
									</div>
									<div style='border-top:1px solid #333;'>
										<p id='thepersonincharge'> 
											<?php 
												if (isset($_POST['signatory'])) {
													echo $_POST['signatory']; 
												}
											?>
											<!--input type='text' style='text-align:center; border:none;' placeholder='name of the person in charge'/--> 
										</p>
									</div> 
								</div>
								
								<!--div style='clear:both;'></div-->
								
								<div style='margin-top:80px;'>
									<table style='width:100%;' class='signatories_tbl'>
										<tr>
											<td style='width:60%;'> 
												<?php if ( $div_sig != null && $division_data[0]->Division_Id != 0 && $division_data[0]->area_id == 1 && $div_sig != $dbm_sig): // $div_sig != null && count($div_data) != 0 && ?>
													Certified Correct: 
												<?php endif; ?>
											</td>
											<?php if($dbm_sig != null): // $area == 1 && ?>
												<td style='width:40%;'> Approved By: </td>
											<?php endif; ?>
										</tr>
										<tr>
											<td> 
												<?php if ( $div_sig != null && $division_data[0]->Division_Id != 0 && $division_data[0]->area_id == 1  && $div_sig != $dbm_sig) { // $div_sig != null && count($div_data) != 0 && ?>
												<div class='ds_signature'>
													<?php
														echo "<img src='".base_url()."/assets/esignatures/{$div_sig}'/>";
													 ?>
												</div>
												<p style='margin-top:30px; margin-bottom:0px; font-size:16px; border-bottom:1px solid #333; float:left;'> <?php echo $division['div_name']; echo $div_name; ?> </p>
												<div style='clear:both;'></div>
												<p style='font-size:13px; padding:0px; margin:0px;'>
													<?php echo $div_data[0]->position_name.", ".$div_data[0]->Division_Desc; ?>
												</p>
												<?php } ?>
											</td>
											<?php if( $dbm_sig != null ): // $area == 1 && $dbm_sig != null && count($dbm_data) != 0?>
											<td>
												<div class='ds_signature'>
													<?php if ($dbm_sig != null) {
														echo "<img src='".base_url()."/assets/esignatures/{$dbm_sig}'/>";
													} ?>
												</div>
												<p style='margin-top:30px; margin-bottom:0px; font-size:16px; border-bottom:1px solid #333; float:left;'> <?php /* echo $dbm['dbm_name']; */ echo $dbm_name; ?> </p>
												<div style='clear:both;'></div>
												<p style='font-size:13px; padding:0px; margin:0px;'>
													<?php echo $dbm_data[0]->position_name.", ".$dbm_data[0]->DBM_Sub_Pap_Desc; ?>
												</p>
											</td>
											<?php   endif; ?>
										</tr>
									</table>
								</div>
							</div>
						</div>
					<!-- accomplishment report -->
					<?php endif; ?>
					
			</div>
		</div>
		
		<div class="modal fade in" id="modal_accomprint" tabindex="-1" role="dialog" aria-labelledby="label_exceptions" aria-hidden="true">
			<div class="modal-lg modal-dialog">
				<div class="modal-content small_width">
					<div class='modal-header'>
						<h4> Select from the DTR Coverage </h4>
					</div>
					<div class="modal-content">
						<div class='modal_body_pad'>
							<select class='btn btn-default' style='width: 100%; padding: 13px; font-size: 15px; font-weight: bold;' id='dtr_select'>
								<?php 
									if (isset($get_cover)) {
										echo "<optgroup label='Select'>
												<option value='default_'> -----  </option>
											  </optgroup>";
										$count = 1;
										foreach($get_cover as $gc) {
											echo "<optgroup label='".$gc->employment_type."'>";
											echo "<option id='cov_{$count}' value='cov_{$count}' data-coverid='{$gc->dtr_cover_id}' data-dedline='".date("l, F d, Y", strtotime($gc->date_deadline))."'>";
													echo $gc->date_started;
														echo " - ";
													echo $gc->date_ended;
												echo "</option>";
											echo "</optgroup>";											
											$count++;
										}
									}
								?>
							</select>
							<p style='margin: 10px 0px;'> <i class="fa fa-bomb"></i> Deadline: <span id='deadlineofsub'> - - </span> </p>
						</div>
					</div>
					<div class='modal-footer'>
						<p id='msg_att_text' style='float: left; margin: 11px;'></p>
						<button class='btn btn-primary' style='margin-top:10px;' id='attachnow'> <i class="fa fa-paperclip"></i> Attach </button>
					</div>
				</div>
			</div>
		</div>
		
	</section>
</div>

<style>
<?php
	if ($notloggedin == true) {
		?>
			.main-header,.main-sidebar {
				display:none;
			}
			
			.content-wrapper {
				margin-Left:0px;
			}
		<?php 
	}
?>
</style>