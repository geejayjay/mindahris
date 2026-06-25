<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
  <title>MinDATa <?php echo $title;?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets_new/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->

  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bower_components/font-awesome/css/font-awesome.min.css">
  <!--link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css"-->  
	

  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets_new/plugins/jvectormap/jquery-jvectormap-1.2.2.css">

    <!-- fullCalendar 2.2.5-->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets_new/plugins/fullcalendar/fullcalendar.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets_new/plugins/fullcalendar/fullcalendar.print.css" media="print">


  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets_new/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets_new/dist/css/skins/_all-skins.min.css">

  <!-- version 2 style -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>v2includes/style/v2.globalstyle.css">
  <!-- end -->

    <!-- jQWidgets CSS -->

  <link href="<?php echo base_url(); ?>assets/jqwidgets/styles/jqx.base.css" rel="stylesheet">
  <link href="<?php echo base_url(); ?>assets/jqwidgets/styles/jqx.bootstrap.css" rel="stylesheet">

  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/jqwidgets/styles/jqx.base.css" type="text/css" />

  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/jquery-jgrowl/1.4.1/jquery.jgrowl.min.css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/style.css" type="text/css" />


<!-- v2 as of Alvin -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/v2.style.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>v2includes/style/newhome.style.css" type="text/css"/>
<!--end-->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.js"></script--> 
	 
  <script src="<?php echo base_url(); ?>assets_new/plugins/jQuery/jquery-2.2.3.min.js"></script>

  
<!-- Bootstrap 3.3.6 -->
  <script src="<?php echo base_url(); ?>assets_new/bootstrap/js/bootstrap.min.js"></script>

 <!-- as of Alvin  -- for ajax function -->
  <script src="<?php echo base_url(); ?>assets/js/v2js/v2.mindajsmodel.js"></script>
 <!-- end -->
  <!--script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script-->
  <script src="<?php echo base_url(); ?>v2includes/js/moment.js"></script>
  <script src="<?php echo base_url(); ?>assets_new/plugins/fullcalendar/fullcalendar.min.js"></script>
  
  <!-- JQWIDGETS -->

    <script src="<?php echo base_url(); ?>assets/js/mindata.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/md5.js"></script>
	
	 <!-- Alvin JS -->
		<script src="<?php echo base_url(); ?>v2includes/js/newhome.proc.js"></script>
	<!-- end alvin js --> 

    <!--script src="//js.pusher.com/2.2/pusher.min.js"></script>
    <script type="text/javascript" src="<?php //echo base_url(); ?>assets/js/pushernotifier.js"></script-->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap-notify.min.js"></script>
    <!--script src="//cdnjs.cloudflare.com/ajax/libs/jquery-jgrowl/1.4.1/jquery.jgrowl.min.js"></script-->


    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxdata.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxscrollbar.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxmenu.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxlistbox.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxdropdownlist.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.selection.js"></script> 
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.columnsresize.js"></script> 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.filter.js"></script> 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.sort.js"></script> 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxcheckbox.js"></script> 
     <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.pager.js"></script> 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.grouping.js"></script> 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxdropdownbutton.js"></script> 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxnumberinput.js"></script> 


    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.edit.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxgrid.export.js"></script> 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxeditor.js"></script> 
 
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxdatatable.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxtreegrid.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxdata.export.js"></script> 


    


    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxdatetimeinput.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxloader.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxpopover.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxcalendar.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxtooltip.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxwindow.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/globalization/globalize.js"></script>



    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxcombobox.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/jqwidgets/jqxfileupload.js"></script>
	
    <!--script type="text/javascript" src="<?php //echo base_url(); ?>v2includes/js/checklogin.procs.js"></script-->
	
    <style>
    .jGrowl-message img{
      width: 35px;
    }    

    .jGrowl-message a{
      text-decoration: none;
    }

    .jGrowl-message p{
       margin-left: 55px;
      width: 100%;
      margin-bottom: 0px;
      font-size: 13px !important;
      color: #fff !important;
    }
    </style>


    <!--  PUSHER -->
    <script type="text/javascript">
	
      var SESSION_ID = '<?php echo $this->session->userdata('employee_id'); ?>';
	/*
      $(function() {
          var pusher = new Pusher('569cc4a878a4b1de8ddc');
          var channel = pusher.subscribe('my_notifications');
          var notifier = new PusherNotifier(channel);
      });
	*/
  </script>


  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script>
	 var BASE_URL = '<?php echo base_url(); ?>';
  </script>
    <?php 
	  
      // var_dump($headscripts);
      if (isset($headscripts)) {
        if (isset($headscripts['style'])) {
           if (is_array($headscripts['style'])) {
             foreach($headscripts['style'] as $style) {
               echo "<link rel='stylesheet' href='{$style}'/>";   
             }   
           } else {
             echo "<link rel='stylesheet' href='".$headscripts['style']."'/>";
           }
        }
         
        if (isset($headscripts['js'])) {
           if (is_array($headscripts['js'])) {
             foreach($headscripts['js'] as $js){
               echo "<script src='{$js}'></script>";   
             }
           } else {
              echo "<script src='".$headscripts['js']."'></script>";   
           }
        }
      }
    ?>

</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
