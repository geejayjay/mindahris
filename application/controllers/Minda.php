<?php 

	class Minda extends CI_Controller {
		public function Calendar($office = '') {
		//	$office 		 = $key;
			$val = $this->session->userdata('cal_in');
		//	var_dump($val); return;
			
			if ($val == NULL) {
				echo "<div style='width: 20%;margin: auto;border: 1px solid #dfdfdf;padding: 10px;border-radius: 3px;'>";
					echo "<h4 style='font-family:arial;'> Please Login </h4>";
					echo "<form method='POST'>";
						echo "<input type='text' name='uname' style='width: 100%;padding: 9px;border: 1px solid #bfbfbf;' placeholder='Username'/>";
							echo "<br/>";
						echo "<input type='password' name='pword' style='width: 100%;padding: 9px;border: 1px solid #bfbfbf;' placeholder='Password'/>";
							echo "<br/>";
						echo "<input type='submit' value='Login' name='loginbtn' style='width: 100%;padding: 9px;border: 1px solid #bfbfbf;background: #e1ffe3;font-size: 14px;'/>";
					echo "</form>";
				echo "</div>";
				if (isset($_POST["loginbtn"])) {
					if ($_POST['uname'] == "minda" && $_POST['pword'] == "minda12345") {
						$newses = array(
							'cal_in'	=> true,
						);
						$this->session->set_userdata($newses);
						redirect(base_url()."minda/calendar","refresh");
					} else {
						echo "User is not recognized";
					}
				}
				
			} else if ($val == true){
				$data['thecal'] = "https://calendar.google.com/calendar/embed?height=600&amp;wkst=1&amp;bgcolor=%23ffffff&amp;ctz=Asia%2FManila&amp;src=bWluZGEuZ292LnBoX3ZkbXVrZzkwajBoMDJwNXNpcmZrOHZrbjdjQGdyb3VwLmNhbGVuZGFyLmdvb2dsZS5jb20&amp;src=bWluZGEuZ292LnBoX2VvMHRkN2oxczJyaGRpZmV2NDVlcGhkMnVnQGdyb3VwLmNhbGVuZGFyLmdvb2dsZS5jb20&amp;color=%23402175&amp;color=%23AA5A00&amp;title=Minda%20Wide%20Calendar";
				if (strlen($office) > 0) {
					$pp 			 = dirname(__FILE__);
					$ppp 			 = explode("/",$pp);
					
					$fpath 			 = $ppp[0]."/".$ppp[1]."/".$ppp[2]."/".$ppp[3]."/uploads/embeds/embeds.csv";
					//echo $fpath; return;
					$tfile  		 = fopen($fpath,"r");

					$proceed 		 = false; 

					while(! feof($tfile)) {
						$csvfile = fgetcsv($tfile);

						$found 	 = false;
						if ($csvfile != false){
							foreach($csvfile as $cs) {
								if ($found) {
								   $data['thecal'] = $cs;
								}

								if ( strtolower($cs) == strtolower($office)){
									$found = $proceed = true;
								}

							}
						}
					}

					if (!$proceed) {
						 die("cannot be found");
					}
				}

				// return view("calendar")->with($data);
			
				$this->load->view("calendar/calendar", $data);
			}
		}
	}

