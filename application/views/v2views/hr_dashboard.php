<div class="content-wrapper" > <!-- style='margin-left: 230px !important;' -->
	<section class="content" style='padding:0px;'>
	
<div class='hr_overall_wrap' >
	<div class='hr_wrapper'>
			<div class='hr_drop_navs'>
			<!-- hr main navigation hr_main_navs leavecabinet-->
				<div class='hr_main_navs leavecabinet'>
					<ul>
						<?php /*if ($admin):*/ $http = base_url(); ?>
						<li title='Leave Management'> 
							<a href='<?php echo $http; ?>/leave/management/' alt='LEAVE MANAGEMENT' target=''> 
								<p>
								<i class="fa fa-tasks" aria-hidden="true"></i>  
								<span> Leave Management </span>
								</p>
							</a> 
						</li>
						<li title='DTR - HR View'> 
							<p data-link='<?php echo $http; ?>/hr/dtr' alt='DTR - HR VIEW'> 
								<i class="fa fa-wpforms" aria-hidden="true"></i>
								<span> DTR (HR-view) </span>
							</p> 
						</li>
						<!--li title='DHV (beta 1)'> 
							<p data-link='<?php //echo $http; ?>/hr/newdtrview' alt='DTR - HR VIEW'> 
								<i class="fa fa-wpforms" aria-hidden="true"></i>
								<span> DHV (beta 1) </span>
							</p> 
						</li-->
						<li title='Print DTR'> 
							<p data-link='<?php echo $http; ?>/hr/displaydtr' alt='Print DTR'> 
								<i class="fa fa-print" aria-hidden="true"></i>
								<span> Print DTR </span>
							</p> 
						</li>
						<!--li title='Summary Reports'> 
							<p data-link='<?php // echo $http; ?>/hr/summary' alt='SUMMARY REPORTS'> 
								<i class="fa fa-list-alt" aria-hidden="true"></i> 
								<span> DTR Coverage </span>
							</p>
						</li-->
						<li title='Summary Reports'> 
							<p data-link='<?php echo $http; ?>/hr/newsummary' alt='SUMMARY REPORTS'> 
								<i class="fa fa-list-alt" aria-hidden="true"></i> 
								<span> DTR Coverage </span>
							</p>
						</li>
						<li>
							<p data-link='<?php echo $http; ?>/hr/reports' alt='REPORTS'> 
								<i class="fa fa-list-alt" aria-hidden="true"></i> 
								<span> Reports </span>
							</p>
						</li>
						
						<?php if ($this->session->userdata("employee_id") == 3563 || 
									$this->session->userdata("employee_id") == 389): ?>
							<li title='Import Log'> <p data-link='<?php echo $http; ?>/hr/importdata'> <i class="fa fa-upload" aria-hidden="true"></i>  </p> </li>
						<?php endif; ?>
						
						<li title='Signature'> <p data-link='<?php echo $http; ?>/hr/signature'> <i class="fa fa-pencil-square-o" aria-hidden="true"></i> </p> </li>
						<li title='Shifts'> <p data-link='<?php echo $http; ?>/hr/shifts'> <i class="fa fa-sitemap" aria-hidden="true"></i>  </p> </li>
						<li title='Schedules'> <p data-link='<?php echo $http; ?>/hr/schedules'> <i class="fa fa-calendar" aria-hidden="true"></i> </p> </li>
						<li title='Offices'> <p data-link='<?php echo $http; ?>/hr/offices'> <i class="fa fa-building" aria-hidden="true"></i>  </p> </li>
						<li title='Areas'> <p data-link='<?php echo $http; ?>/hr/areas'> <i class="fa fa-map-o" aria-hidden="true"></i>  </p> </li>
						<li title='Positions'> <p data-link='<?php echo $http; ?>/hr/positions'> <i class="fa fa-address-card-o" aria-hidden="true"></i>  </p> </li>
						<li title='Employees'> <p data-link='<?php echo $http; ?>/hr/employees'> <i class="fa fa-users" aria-hidden="true"></i>  </p> </li>
						<li title='Holidays'> <p data-link='<?php echo $http; ?>/leaveadministration/holidays'> <i class="fa fa-sun-o" aria-hidden="true"></i>  </p> </li>
						<li title='Sync Bio' id='sync_bios'> 
						<p> <i class="fa fa-refresh" aria-hidden="true"></i>  </p> 
							<ul>
								<?php 
									// $url = "https://".$_SERVER['HTTP_HOST']."/hr/dashboard";
									$url 	= base_url()."/hr/dashboard";
									foreach($syncs as $lis) {
										echo "<li>
											<a href='{$url}/getfrom/{$lis['code']}'>";
										echo "<p><i class='fa fa-map-signs' aria-hidden='true'></i> &nbsp;";
											echo "<span>{$lis['name']}</span> ";
											// echo "<span>{$lis['code']}</span> | ";
											echo "<span>[{$lis['ip']}]</span> ";
											echo "<span style='font-size: 13px; color: #847474; float: right; margin-top: -9px;'>
													<strong style='font-size: 11px !important; float: right;'>Last Update:</strong> <br/> 
													<i class='fa fa-calendar' aria-hidden='true'></i> {$lis['last_update']}
												  </span>";
										echo "</p></a>
											</li>";
									}
								?>
							</ul>
						</li>
							
						<!--li>
							<p> <i class="fa fa-refresh" aria-hidden="true"></i>  </p> 
							<ul>
								<?php 
									/*
									foreach($syncs as $er) {
										echo "<li>";
											echo $er['name'];
										echo "</li>";
									}
									*/
								?>
								<i class="fas fa-eraser"></i>
							</ul>
						</li-->
						<?php if(!$admin): ?>
							<!--li> <p data-link='<?php // echo $http; ?>/hr/dtr'> <i class="fa fa-wpforms" aria-hidden="true"></i> My DTR </p> </li-->
						<?php endif; ?>
					</ul>
				</div> 
			<!-- end of hr main navigation -->
			</div>
			
		<!-- used in loading the DTR -->
		<span id='element_loader'></span>
		<!-- end loading DTR -->
		
		<?php 
			
			if (isset($issaved)) {
				if ($issaved != false) {
					echo "<p style='padding: 33px 0px 0px; text-align: center;'> Data in database has been successfully synchronized with the biometrics' at ".$issaved.".</p>";
					echo "<p style='text-align:center;'> WARNING: reloading this page means you are trying to reprocess the transaction again. </p>";
					echo "<p style='text-align:center;'> To view DTR click <a href='{$url}'>here</a> </p>";
				} else {
					echo "<p> There was an error. </p>";
				}
			}
		?>
		<div class='showwindow' id='showwindow'>
			<div id='pop_upwindow'>
				
			</div>
		</div>
	</div>
</div>

	</section>
</div>
