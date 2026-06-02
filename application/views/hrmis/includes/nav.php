  <header class="main-header">
    <?php 
        $usertype = $this->session->userdata('usertype');
		$empid    = $this->session->userdata('employee_id');
        $display_1 = 'style="display:none;"';
        
    ?>
	<?php //$url = "http://".$_SERVER['HTTP_HOST']; 
		$url = base_url();
	?>
    <!-- Logo -->
    <a href="<?php echo $url; ?>" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini" style="font-size: 18px; font-family: calibri;"><b>HR</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg" style="font-size:18px; font-family: calibri;"><b>MinDA</b>Ta <?php echo '-  <small>('.$this->session->userdata('area_name').')</small> '?></span>
    </a>

    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
		
	  <ul class='new_navigation'> <!-- "usertype == admin" hr-admin-btn -->	
		<?php if (isset($admin) && $admin) { ?>
			<li class='hradmin hr-admin-btn' data-href='<?php echo $url; ?>/hr/dashboard'/> 
				<i class="fa fa-tachometer" aria-hidden="true"></i> 
				<span> HR - Admin </span>
			</li>
			<!--li class='hradmin returneddtr' data-href='<?php // echo $url; ?>/dtr/returned'> <i class="fa fa-file-archive-o" aria-hidden="true"></i> &nbsp; DTR for approval </li-->
			<li class='hradmin' data-href='<?php echo $url; ?>/dtr/review'> 
				<i class="fa fa-file-archive-o" aria-hidden="true"></i> 
					<span> HR: DTR for Review </span>
				</li>
		<?php } ?>
			<!--li class='hradmin hr-admin-btn' data-href='<?php // echo $url; ?>/my/dashboard'/> <i class="fa fa-tachometer" aria-hidden="true"></i> &nbsp; Personal </li-->	
		<li class='hradmin cabinet-btn' data-href='<?php echo $url; ?>'> 
			<i class="fa fa-archive" aria-hidden="true"></i> 
				<span> Leave Cabinet </span>
		</li>
		<?php $led_url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>
		<a href='<?php echo $url; ?>my/ledger/<?php echo $this->session->userdata("employee_id"); ?>'/> 
			<li class='leave_ledger_btn' data-href='<?php //echo $url; ?>/my/ledger/'> 
				<i class="fa fa-book" aria-hidden="true"></i>
					<span> Leave Ledger </span>
			</li>
		</a>
		<?php 
			
			if ( $this->session->userdata('isfocal') == 1 ) {
				echo "<a href='{$url}dtr/forapproval' target='_blank'/>
						<li class='leave_ledger_btn'> 
							<i class='fa fa-folder-o' aria-hidden='true'></i> 
								<span> Submitted DTR's </span>
						</li>
					  </a>
					";
			}
		?>
		
		<?php 
		/*
			$this->load->model("v2main/Globalproc");
			
			$isfocal = $this->Globalproc->gdtf("employees",['employee_id'=>$empid],"isfocal");
			
			if (count($isfocal)>0) {
				if ($isfocal[0]->isfocal == true) {
					*/
		?>
			<!--a href='<?php //echo $url; ?>Hr/managetimelog'>
				<li class='leave_ledger_btn'> 
					<i class="fa fa-toggle-on" aria-hidden="true"></i>
					<span> Timelog Management </span> 
				</li>
			</a-->
				<?php //} } ?>
		<?php 
			if (isset($admin) && $admin){
				echo "<a href='{$url}/Hr/utilities'>";
					echo"<li class='leave_ledger_btn'>";
						echo "<i class='fa fa-toggle-on' aria-hidden='true'></i>";
						echo "<span> HR Utilities </span>";
						echo "</li>";
				echo "</a>";
			}
		
		?>
		
		<?php 
			if (isset($admin) && $admin) { 
				echo "<a href='{$url}/onlinedirectory/'>";
				//echo "<a href='{$url}/pds/administration'>";
					echo "<li class='leave_ledger_btn'> 
							<i class='fa fa-sticky-note' aria-hidden='true'></i>
							<span> PDS Administration </span> 
						  </li>";
				echo "</a>";
			}
		?>
		
		<?php 
			if (isset($admin) && $admin) { 
				echo "<a href='{$url}/pds/applicants'>";
					echo "<li class='leave_ledger_btn'> 
							<i class='fa fa-sticky-note' aria-hidden='true'></i>
							<span> Applicants </span> 
						  </li>";
				echo "</a>";
			}
		?>
		
	  </ul> <!-- /leave/cabinet -->
	  
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <?php 
              $employee_image = $this->session->userdata('employee_image');
			  
              if( strlen($employee_image) > 1 ){
                  $image_url = base_url().'/assets/profiles/'.$employee_image;
              }else{
                   $image_url =  base_url().'/assets/images/userImage.gif';
              }
          ?>

          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo $image_url; ?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $this->session->userdata('username'); ?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo $image_url; ?>" class="img-circle" alt="User Image">

                <p>
                 <?php echo ucwords(strtolower($this->session->userdata('full_name'))); ?> 
                  <small> <?php echo $this->session->userdata('position_name'); ?></small>
                  <small><?php echo $this->session->userdata('usertype'); ?></small>
                </p>
              </li>
              <!-- Menu Body -->
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="<?php echo base_url(); ?>accounts/logout" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>
        </ul>
      </div>

    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?php echo $image_url; ?>" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php echo ucwords(strtolower($this->session->userdata('first_name'))); ?> <?php echo ucwords(strtolower($this->session->userdata('last_name'))); ?>  </p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->


      <ul class="sidebar-menu">
        <li class="header">MAIN NAVIGATION</li>

        <!--li class="treeview <?php //if($title == '| Dashboard'){ echo 'active' ; }?>">
        <a href="<?php// echo base_url(); ?>dashboard"><span>Dashboard</span>
        <span class="pull-right-container">
              <small class="label pull-right bg-red">n/a</small>
        </span> 
        </a>
        </li-->
		
		<!--li class="treeview <?php // if($title == '| My Documents'){ echo 'active' ; }?>">
            <a href="<?php // echo base_url(); ?>my/documents"> <span>My Documents</span>
            <span class="pull-right-container"></span> </a>
        </li--> 
        <li class="treeview <?php if($title == '| Profile'){ echo 'active' ; }?>">
            <a href="<?php echo base_url(); ?>personnel/profile"> <span>My Profile</span>
            <span class="pull-right-container"></span> </a>
        </li> 
        <li  class="treeview <?php if($title == '| Daily Time Records'){ echo 'active' ; }?> ">
            <!--a href="<?php //echo base_url(); ?>reports/dtr"><i class="fa fa-files-o fa-fw"></i>  <span>My Daily Time Records</span-->
			<a href="<?php echo base_url(); ?>my/dtr"><span>My DTR</span> <!-- dashboard :: my/dailytimerecords-->
            <span class="pull-right-container"></span> </a>
        </li>
		
		<li  class="treeview <?php if($title == '| PDS'){ echo 'active' ; }?> ">
            <!--a href="<?php //echo base_url(); ?>reports/dtr"><i class="fa fa-files-o fa-fw"></i>  <span>My Daily Time Records</span-->
			<a href="<?php echo base_url(); ?>pds"><span>My PDS</span> <!-- dashboard -->
            <span class="pull-right-container"></span> </a>
        </li>
		
		<li  class="treeview <?php if($title == '| Travel Order'){ echo 'active' ; }?> ">
            <!--a href="<?php //echo base_url(); ?>reports/dtr"><i class="fa fa-files-o fa-fw"></i>  <span>My Daily Time Records</span-->
			<a href="<?php echo base_url(); ?>my/travelorders"><span>Travel Order</span> <!-- dashboard -->
            <span class="pull-right-container"></span> </a>
        </li>
		
		<li class="header"> Verification </li>
		<li class='treeview'>
			<a href="<?php echo base_url(); ?>verify" target="_blank"> <span> Verify transaction </span></a>
		</li>
		
		<?php if($this->session->userdata('is_head') == 1): ?>
		<li  class="treeview <?php if($title == '| For Approval'){ echo 'active' ; }?> ">
            <!--a href="<?php //echo base_url(); ?>reports/dtr"><i class="fa fa-files-o fa-fw"></i>  <span>My Daily Time Records</span-->
			<a href="<?php echo base_url(); ?>dtr/forapproval"><span> For Approval (DTR) </span> <!-- dashboard -->
            <span class="pull-right-container"></span> </a>
        </li>
		<?php endif; ?>
		
		<?php //if($this->session->userdata('employment_type') == "JO"): ?>
		<li class='header'> Daily Task </li>
		 <li  class="treeview <?php if($title == '| Accomplishment'){ echo 'active' ; }?> ">
            <!--a href="<?php //echo base_url(); ?>reports/dtr"><i class="fa fa-files-o fa-fw"></i>  <span>My Daily Time Records</span-->
			<a href="<?php echo base_url(); ?>my/accomplishments/print"><span>Daily Accomplishment Report</span> <!-- dashboard -->
            <span class="pull-right-container"></span> </a>
        </li>
		<?php // endif; ?>
        <!--li  class="treeview <?php //if($title == '| Ledger'){ echo 'active' ; }?> ">
            <a href="<?php //echo base_url(); ?>leaveadministration/ledger"><span>My Leave Ledger</span>
			<!--a href="<?php //echo base_url(); ?>leave/management/"><span> My Leave Ledger </span></a-->
			
            <!--span class="pull-right-container"></span> </a>
        </li-->
        <!--li class="treeview <?php //if($title == '| Applications'){ echo 'active' ; }?> ">
            <a href="<?php // echo base_url(); ?>reports/applications"> <span>My Leave Applications</span>
            <span class="pull-right-container"></span> </a>
        </li-->


        <?php if($this->session->userdata('position_name') == 'Security' || $usertype == 'admin') { ?>
        <li class="header">GUARD NAVIGATION</li>
        <li  class="treeview <?php if($title == '| AMS/PS Monitoring'){ echo 'active' ; }?>" <?php if($usertype == 'admin'){ echo ''; }else if ($usertype == 'user'){  echo 'style=""'; } else if ($usertype == 'f-admin'){  echo 'style=""'; }?> >
            <a href="<?php echo base_url(); ?>monitoring/dashboard"> <span>Monitoring AMS/PS</span>
            <span class="pull-right-container"></span> </a>
        </li>
        <?php } ?>


        <?php if($usertype == 'admin') { ?>

                   <!--li class="header">ADMIN NAVIGATION</li>
                   <li class="treeview" style="display:none;">
                        <a href="<?php //echo base_url(); ?>dashboard"> <i class="fa fa-dashboard"></i> <span>Admin Dashboard</span>
                        <span class="pull-right-container"></span> <small class="label pull-right bg-red">n/a</small> </a>
                        </li>
                        <li class="treeview <?php// if($title == '| Area Setup' || $title == '| Division Setup' || $title == '| Positions' || $title == '| Employees' ){ echo 'active' ; }?>">
                            <a href="<?php //echo base_url(); ?>personnel"><i class="fa fa-user fa-fw"></i>  <span>Personnel</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span></a>
                            <ul class="treeview-menu">
                                <li class="<?php// if($title == '| Area Setup'){ echo 'active' ; }?>">
                                    <a href="<?php //echo base_url(); ?>personnel/areas"><i class="fa fa-circle-o"></i>Areas</a>
                                </li>
                                <li class="<?php// if($title == '| Division Setup'){ echo 'active' ; }?>">
                                    <a href="<?php// echo base_url(); ?>personnel/officedivision"><i class="fa fa-circle-o"></i>Offices & Divisions</a>
                                </li>
                                <li class="<?php// if($title == '| Positions'){ echo 'active' ; }?>">
                                    <a href="<?php //echo base_url(); ?>personnel/position"><i class="fa fa-circle-o"></i>Positions</a>
                                </li>
                                <li class="<?php// if($title == '| Employees'){ echo 'active' ; }?>">
                                    <a href="<?php// echo base_url(); ?>personnel/employee"><i class="fa fa-circle-o"></i>Employees</a>
                                </li>
                            </ul>
                            
                        </li>
                        <li class="treeview  <?php// if($title == '| Import Timelogs' || $title == '| Attendance Record' || $title == '| Shift Management' || $title == '| Employee Schedule'){ echo 'active'; } ?>">
                            <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i>   <span>Attendance</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span></a>
                            <ul class="treeview-menu">
                                <li  class="<?php// if($title == '| Import Timelogs'){ echo 'active' ; }?>">
                                    <a href="<?php //echo base_url(); ?>attendance/importdata"><i class="fa fa-circle-o"></i>Import Timelogs</a>
                                </li>
                                <li class="<?php //if($title == '| Attendance Record'){ echo 'active' ; }?>">
                                    <a href="<?php// echo base_url(); ?>hr/dashboard"><i class="fa fa-circle-o"></i>Attendance Log</a>
									
                                </li>
                                <li class="<?php// if($title == '| Shift Management'){ echo 'active' ; }?>">
                                    <a href="<?php //echo base_url(); ?>attendance/shiftmanagement"><i class="fa fa-circle-o"></i>Shift Management</a>
                                </li>                                  
                                <li class="<?php //if($title == '| Employee Schedule'){ echo 'active' ; }?>">
                                    <a href="<?php //echo base_url(); ?>attendance/employeeschedule"><i class="fa fa-circle-o"></i>Employee Schedule</a>
                                </li>

                            </ul>
                        </li>
                        <li  class="treeview <?php// if($title == '| Daily Time Records' || $title == '| Summary Reports'){ echo 'active'; } ?>">
                            <a href="#"><i class="fa fa-files-o fa-fw"></i> <span>Reports</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span></a>
                            <ul class="treeview-menu">
                                <li class="<?php// if($title == '| Daily Time Records'){ echo 'active' ; }?>">
                                    <a href="<?php //echo base_url(); ?>reports/dtr"><i class="fa fa-circle-o"></i>Daily Time Records</a>
                                </li>
                                <li class="<?php// if($title == '| Summary Reports'){ echo 'active' ; }?>">
                                    <a href="<?php// echo base_url(); ?>reports/summary"><i class="fa fa-circle-o"></i>Summary Reports</a>
                                </li>
                            </ul>
                        </li-->
						
                        <!--li class="treeview <?php// if($title == '| Ledger' || $title == '| Holidays'){ echo 'active'; } ?>">
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i><span>Leave Administration</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span></a>
                            <ul class="treeview-menu">
                                <li class="<?php //if($title == '| Ledger'){ echo 'active' ; }?>">
                                    <a href="<?php// echo base_url(); ?>leaveadministration/ledger">Leave Ledger</a>
                                </li>                                
                                <li class="<?php// if($title == '| Holidays'){ echo 'active' ; }?>">
                                    <a href="<?php //echo base_url(); ?>leaveadministration/holidays">Holidays</a>
                                </li>
                            </ul>
                            
                        </li-->
						<li class="header">MAINTENANCE</li>
                        <li class="treeview <?php if($title == '| Database Settings' || $title == '| Holidays'){ echo 'active'; } ?>" >
                            <a href="#"> <span>System</span>
                            <span class="pull-right-container">
                            </span></a>
                            <ul class="treeview-menu">
                                <!--li class="<?php // if($title == '| Database Settings'){ echo 'active'; }?>">
                                    <a href="<?php //echo base_url(); ?>systemsettings/databasesettings">Database Settings</a>
                                </li-->
								<li class="<?php if($title == '| Biometric Device Settings'){ echo 'active'; }?>">
                                    <a href="<?php echo base_url(); ?>systemsettings/biometricsettings">Biometric Device Settings</a>
                                </li>
                                <li style="display:none;" class="<?php if($title == '| Holidays'){ echo 'active' ; }?>">
                                    <a href="<?php echo base_url(); ?>systemsettings/importexport">Pusher Settings</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                <?php } ?> 
				 <!--li>
                    <a href="<?php //echo base_url(); ?>systemsettings/databasesettings">Database Settings</a>
                 </li-->
				 <li class='header'> Feedback </li>
				 <li>
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSc3ZbWFU2tCjtUaTrq6gDqkpFHpOOpQAaBZSTew3-oB6geKXg/viewform?usp=sf_link" target="_blank">Send us your feedback</a>
                 </li>
				 <li class='header'> Training Videos </li>
				 <li>
					<!--a href='https://drive.google.com/file/d/1vIx_GkO_f55KmWGLUp7eCFeJbqIcyfGk/view?usp=sharing' target='_blank'/> Submission of DTR </a-->
					<a href='<?php echo base_url()."/assets/videos/submission of dtr and leave ledger.mp4"; ?>' target='_blank'> Submission of DTR </a>
				 </li>
				 <li>
					<!--a href='https://drive.google.com/file/d/1FGpI9FLdEwyR_FE9iVY4gfrYFv942Fep/view?usp=sharing' target='_blank'/> Application of Leave, Pass slip etc. </a-->
					<a href='<?php echo base_url()."/assets/videos/application of leave.mp4"; ?>' target='_blank'> Application of Leave, etc. </a>
				 </li>
				 <li>
					<!--a href='https://drive.google.com/file/d/1FGpI9FLdEwyR_FE9iVY4gfrYFv942Fep/view?usp=sharing' target='_blank'/> Application of Leave, Pass slip etc. </a-->
					<a href='<?php echo base_url()."/assets/videos/coc.mp4"; ?>' target='_blank'> Application of COC </a>
				 </li>
				  <li>
					<!--a href='https://drive.google.com/file/d/1FGpI9FLdEwyR_FE9iVY4gfrYFv942Fep/view?usp=sharing' target='_blank'/> Application of Leave, Pass slip etc. </a-->
					<a href='<?php echo base_url()."/assets/videos/OT.mp4"; ?>' target='_blank'> Application of Overtime </a>
				 </li>
                <!--  end admin -->      

      </ul>

      
    </section>
    <!-- /.sidebar -->
  </aside>

  <script>

	var BASE_URL = "<?php echo base_url(); ?>";
	
  $( document ).ready(function() {

    getactivities();
   
    $('#button_click_activity ').on('click',function(){

        if($('#label_count_activity').html() == ''){
        }else{
          $('#label_count_activity').html('');
           var session_employee_id = '<?php echo $this->session->userdata('employee_id'); ?>';
           info['employee_id'] = session_employee_id;

           var result = postdata(BASE_URL + 'app/updatenotification' , info);
           if(result){

           }
        }
        
    });

  });



  function getactivities(){

      var echo = '';
      var count_activity = 0;
      info = {};
      var result = postdata(BASE_URL + 'app/getactivities' , info);

      $("#ul_activities").empty(); 

      var get_activities = result.data;

      if(get_activities){

            for (i in get_activities){


              echo +='<li>';
              echo +='<a target="_blank" href="'+get_activities[i]['link']+'" style="white-space: normal;">';
                  echo +='<div class="pull-left">';
                    echo +='<img src="'+get_activities[i]['employee_image']+'" class="img-circle" alt="User Image">';
                  echo +='</div>';
                  echo +='<p style="color:black;font-size:15px;">'+get_activities[i]['content'] +'</p>';
                  echo +='<p><small><i class="fa fa-clock-o"></i>   ' + get_activities[i]['date_added'] + '</small></p>';
              echo +='</a>';
              echo +='</li>';              
            }

          $("#ul_activities").append(echo);
         
      }
        count_activity = result.total_notification;
        $('#label_count_activity').html('');
        if(count_activity != 0){
           $('#label_count_activity').html(count_activity);
        }

  }



</script>
