<html>
	<head>
		<link rel="stylesheet" href="<?php echo base_url(); ?>v2includes/pds/style/printstyle.css">
	</head>
	<body>
		<table class="pi2">
			<thead>
				<tr>
					<th rowspan="2" class="remborr bgme"> 26 </th>
					<th rowspan="2" class="bgme"> LEVEL </th>
					<th rowspan="2" style="width: 285px;" class="bgme"> NAME OF SCHOOL <br>
						<small> Write in full </small>
					</th>
					<th rowspan="2" style="width: 285px;" class="bgme"> BASIC EDUCATION/DEGREE/COURSE <br>
						<small> Write in full </small>
					</th>
					<th colspan="2" class="bgme"> PERIOD OF ATTENDANCE </th>
					<th rowspan="2" class="bgme"> HIGHEST LEVEL/UNITS EARNED <br>
						<small> (if not graduated) </small>
					</th>
					<th rowspan="2" class="bgme"> YEAR GRADUATED </th>
					<th rowspan="2" class="bgme"> SCHOLARSHIP/ACADEMIC HONORS RECEIVED </th>
				</tr>
				<tr class=""> 
					<th style="width: 50px;" class="bgme"> From </th>
					<th style="width: 50px;" class="bgme"> to </th>
				</tr>
			</thead>
			<tbody>
			
				<!--tr class="border_b educbgtd">
					<td class="border_r bgme" colspan="2" style="text-align:center;"> ELEMENTARY </td>
					<td class="border_r"> xcvbcvb </td>
					<td class="border_r"> cvbcvb </td>
					<td class="border_r"> 2021 </td>
					<td class="border_r"> 2021 </td>
					<td class="border_r"> 4534 </td>
					<td class="border_r"> 534534 </td>
					<td class="border_r"> dfgdfgdfg </td>
				</tr-->
				<?php 
					foreach($data as $d) {
						$fromyear = date("Y",strtotime($d->from_));
						$toyear   = date("Y",strtotime($d->to_));
												
					//	if ($fromyear == "1900") { $fromyear = null; }
					//	if ($toyear == "1900") { $toyear = null; }
												
						echo "<tr class='border_b educbgtd'>
								<td class='border_r bgme' colspan='2' style='text-align:center;'> {$d->educbgtype} </td>
								<td class='border_r'> {$d->nameofsch} </td>
								<td class='border_r'> {$d->course} </td>
								<td class='border_r'> {$d->from_} </td>
								<td class='border_r'> {$d->to_} </td>
								<td class='border_r'> {$d->hlevel_unitsearned} </td>
								<td class='border_r'> {$d->yeargrad} </td>
								<td class='border_r'> {$d->scho_honorrec} </td>
							</tr>";
		
					}
				?>
			</tbody>
		</table>
	</body>
</html>