<?php if ($notloggedin == false) { ?>
<?php 
	if (isset($nopdsinput) && $nopdsinput == true) { ?>
	<div class='noinputsyet'>
		<h2> You have not created your PDS yet </h2>
		<h5> Please click create to start editing your PDS </h5>
		<button id='createbtn'> create </button>	
		<hr/>
		<small> the system will automatically save what you inputted right after you leave every boxes </small>
	</div>
<?php } else { ?>
	<div class='hasinput'>
		<h2> Welcome </h2>
		<h6> if you have not completed your PDS yet, please complete it now. </h6>
		<!--hr/-->
		
		<?php 
			if (count($zeroes)==0) {
				//echo "<button class='btn btn-primary printbtn' id='printbtn' data-print='all'> View entire PDS</button>";
			} else {
				echo "<p class='inc'>You have incomplete fields that needs filling up.</p>";
			}
		?>
		
		
		<iframe id='printablearea' name='printablearea'></iframe>
		
		<hr/>
		
		<?php 
			// c1 -------------------
			$dispc1 = true;
			foreach($zeroes as $z) {
				if ($z == "personalinformation" || $z == "educbg" || $z == "familybg" || $z == "addresses") {
					$dispc1 = false;
				}
			}
			
			if ($dispc1 == true) {
				echo "<p><button class='btn btn-primary btn-sm printbtn' data-print='c1'> Print C1 </button></p>";
			} else {
				echo "<p class='inc'>please complete your C1 to be able to print</p>";
			}
			
			// ------------------
			
			// c2---------------
			$dispc2 = true;
			foreach($zeroes as $z) {
				if ($z == "eligibility" || $z == "workexp") {
					$dispc2 = false;
				}
			}
			
			if ($dispc2 == true) {
				echo "<p><button class='btn btn-primary btn-sm printbtn' data-print='c2'> Print C2 </button></p>";
			} else {
				echo "<p class='inc'>please complete your C2 to be able to print</p>";
			}
			
			// c3--------------
			$dispc3 = true;
			foreach($zeroes as $z) {
				if ($z == "seminars") {
					$dispc3 = false;
				}
			}
			
			if ($dispc3 == true) {
				echo "<p><button class='btn btn-primary btn-sm printbtn' data-print='c3'> Print C3 </button></p>";
			} else {
				echo "<p class='inc'>please complete your C3 to be able to print</p>";
			}
			
			// c4--------------
			$dispc4 = true;
			foreach($zeroes as $z) {
				if ($z == "questiontbl" || $z == "reference" || $z == "identification") {
					$dispc4 = false;
				}
			}
			
			if ($dispc4 == true) {
				echo "<p><button class='btn btn-primary btn-sm printbtn' data-print='c4'> Print C4 </button></p>";
			} else {
				echo "<p class='inc'>please complete your C4 to be able to print</p>";
			}
			
		?>
			<span id='loadingtxt'>  </span>
	</div>
<?php }	?>
<?php }	?>

<?php if($notloggedin): ?>
	<div class='noinputsyet'>
		<h2> You are not logged in </h2>
		<h5> please use your HRIS account to login </h5>
		<hr/>
			<a href='<?php echo base_url(); ?>accounts/login' target='_blank'> Login </a>
		<br/>
		<br/>
		<br/>
			<small> you will be redirected to another page to login your hris account. </small> 
			<small>	After you logged in there, please go back to this page and refresh. </small>
	</div>
<?php endif; ?>
