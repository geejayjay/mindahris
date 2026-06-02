<title> Verification </title>
<style>
	body {
		margin:0px;
		font-family:arial;
		background:#f1f1f1;
	}
	
	.maindiv {
		padding:10px;
	}
	
	.searchdiv {
		width:50%;
		margin:auto;
	}
	
	.thesmethod {
		overflow: hidden;
		border: 10px solid #d6d6d6;
	}
	
	.thesmethod select,
	.thesmethod input[type='text'] {
		padding: 5px 20px;
		font-size: 18px;
		height: 75px;
	}
	
	.thesmethod select {
		width:28%;
		margin-right: -6px;
		font-weight:bold;
		text-transform:uppercase;
	}
	
	.thesmethod input[type='text'] {
		width: 72.111111%;
	}
	
	.tblfound {
		width:100%;
		border-collapse: collapse;
		text-align:left;
	}
	
	.tblfound thead{
		border-bottom: 2px solid #333;
	}
	
	.tblfound thead th{
		padding:10px;
	}
	
	.tblfound tbody{
		
	}
	
	.tblfound tbody tr{
		
	}
	
	.tblfound tbody tr:nth-child(2n+2){
		background:#e4e4e4;
	}
	
	.tblfound tbody tr td{
		padding:10px;
		color: #248824;
		font-size: 14px;
	}
	
	form {
	    margin: 0px;
	}
</style>

<div class='maindiv'>
	<div class='searchdiv'>
		<div class='thesmethod'>
			<form method='post' name='categoryform'>
				<select name='categorytype' id='categoryselect'>
					<option value= 'ps' <?php echo ($data['ct']=='ps')?"selected":""; ?>> Pass slip </option>
					<option value='lvcto' <?php echo ($data['ct']=='lvcto')?"selected":""; ?>> Leave/CTO </option>
					<option value='ot' <?php echo ($data['ct']=='ot')?"selected":""; ?>> Overtime </option>
					<option value='paf' <?php echo ($data['ct']=='paf')?"selected":""; ?>> Paf </option>
					<option value='dtr' <?php echo ($data['ct']=='dtr')?"selected":""; ?>> DTR </option>
					<option value='to' <?php echo ($data['ct']=='to')?"selected":""; ?>> Travel Order </option>
				</select>
				<input type='text' name='thebarcode' placeholder='Barcode ex. 20931-32-3292' value='<?php echo $data['bc']; ?>'/>
			</form>
		</div>
		
		<hr style='margin:30px 0px;'/>
		<p> Details: </p>
		<table class='tblfound'>
			<thead>
				<th> NAME </th>
				<th> APPLICATION DATE </th>
				<!--th> RECOMMENDING APPROVAL </th>
				<th> APPROVED BY </th-->
			</thead>
			<tbody>
				<?php
				
					if (isset($data['name'])) {
						$tdcount = $count = count($data['name'])-1;
						for($i = 0 ; $i <= $tdcount; $i++) {
							echo "<tr>";
								echo "<td>";
									echo $data['name'][$count];
								echo "</td>";
								echo "<td>";
									echo $data['date'][$count];
								echo "</td>";
								/*
								echo "<td>";
									echo $data['recom'][$count];
								echo "</td>";
								echo "<td>";
									echo $data['appr'][$count];
								echo "</td>";
								*/
							echo "</tr>";
							$count -= 1;
						}
					} else {
						if ($data != NULL) {
							echo "<tr><td colspan=4> ";
							echo "Do data found with that barcode";
							echo "</td></tr>";
						} else {
							echo "<tr><td colspan=4 style='text-align:center;'> ";
							echo " -- Please select from the category and enter the barcode number -- ";
							echo "</td></tr>";
						}
					}
					/*
					foreach($data as $d) {
						echo "<tr>";
							echo "<td>";
								echo $d->name;
							echo "</td>";
							echo "<td>";
								echo $d->date;
							echo "</td>";
							echo "<td>";
								echo $d->recom;
							echo "</td>";
							echo "<td>";
								echo $d->appr;
							echo "</td>";
						echo "</tr>";
					}
					*/
				?>
			</tbody>
		</table>
	</div>
</div>

<script>
	window.onload = function() {
		var cat = document.getElementById("categoryselect");
		
		cat.addEventListener('input',function(evt){
			var val = this.value;
			
			if (val == 'to') {
				window.location.href = "<?php echo base_url(); ?>verify/travelorder/";
			} else {
				window.location.href = "<?php echo base_url(); ?>verify";
			}
		});	
	}
	
</script>