<?php 
	
	class Googlecalendar extends CI_Controller {
		
		//public $google_client_id;
		
		public function addtocal() {
			// Include database configuration file 
			// include_once "googleauth.php";
			// include_once "calendarprocs.php";
			$this->load->model("Calendarmodel"); 

			$postData = $statusMsg = $valErr = ''; 
			$status = 'danger'; 
			 
			// If the form is submitted 
			if(isset($_POST['submit'])){
				// Get event info 
				$_SESSION['postData'] = $_POST; 
				
				$this->load->model("v2main/Globalproc");
				
				$owner    = $this->session->userdata('employee_id');
				$fullname = $this->session->userdata("full_name");
				
				list($start,$anchor,$year,$month,$count) = explode("-",$_POST['contnum']);
				
				$officeunder = $_POST['officeunder'];
				$colorid     = 0;
				switch($officeunder) {
					case "OC":		$colorid = 10; break;
					case "OED":		$colorid = 4; break;
					case "DED":		$colorid = 9; break;
					case "OFAS":	$colorid = 5; break;
					case "PPPDO":	$colorid = 7; break;
					case "IPPAO":	$colorid = 11; break;
					case "AMO":		$colorid = 6; break;
					case "IPURE":	$colorid = 8; break;
				}
			
				// Insert data into the database 
				$values = [	"datefiled"		=> date("m/d/Y H:i:s A"),
							"controlno"		=> $_POST['contnum'],
							"nameoftravs"	=> $_POST['nmoftravs'],
							"dateoftrav"	=> $_POST['dtoftrav'],
							"dateoftrav_to" => $_POST['dtoftrav_to'],
							"natoftrav"		=> $_POST['natoftrav'],
							"destination"	=> $_POST['dest'],
							"purpose"		=> $_POST['purpose'],
							"reqby"			=> $_POST['reqby'],
							"status"		=> 0,
							"isforeign"     => $_POST['isforeign'],
							"owner"			=> $owner,
							"theyear"		=> $year,
							"thecount"		=> $count,
							"theoffice"		=> $anchor,
							"themonth"		=> $month,
							"attfile"		=> $basename,
							"location"		=> $target_file,
							"encodedby"		=> $fullname,
							"officeunder"	=> $officeunder,
							"colorid"		=> $colorid];
							
				$save 	   = $this->Globalproc->__save("travelorders",$values);
				
				$insert_id = $this->Globalproc->getrecentsavedrecord("travelorders","travelid")[0]->travelid;
				
				if($save){
					 $event_id = $insert_id; 
				   // $event_id   = date("mdyhisa");
						 
					unset($_SESSION['postData']); 
				
					// Store event ID in session 
					$_SESSION['last_event_id'] = $event_id; 
						
					$googleOauthURL = $this->Calendarmodel->googleOauthURL;
					header("Location: $googleOauthURL"); 
					exit(); 
				}else{ 
					$statusMsg = 'Something went wrong, please try again after some time.'; 
				}
			}else{ 
				$statusMsg = 'Form submission failed!'; 
			} 
			 
			$_SESSION['status_response'] = array('status' => $status, 'status_msg' => $statusMsg); 
			
			var_dump($_SESSION); die();
			//header("Location: index.php"); 
			exit(); 
		}
		
		public function startsync() {
			// Include Google calendar api handler class 
			$this->load->model("Calendarmodel"); 
			$this->load->model("v2main/Globalproc");
			
			// Include database configuration file 
			//require_once 'dbConfig.php'; 
			//	include_once "googleauth.php";

			$statusMsg = ''; 
			$status = 'danger'; 
			if(isset($_GET['code'])){
				// Initialize Google Calendar API class 
				// $GoogleCalendarApi = new GoogleCalendarApi(); 
				 
				// Get event ID from session 
				$event_id = $_SESSION['last_event_id']; 
				
				if(!empty($event_id)){ 
				   // Fetch event details from database 
					$eventData = $this->Globalproc->gdtf("travelorders",["toid"=>$event_id],"*");
					//$eventData = true;

					if($eventData){ 
						$calendar_event = array( 
							'summary' 		=> "[HR] Travel of ".$eventData[0]->nameoftravs, 
							'location' 		=> $eventData[0]->destination, 
							'description' 	=> $eventData[0]->purpose ." <br/><br/> TO Number: ". $eventData[0]->controlno,
							'colorid'		=> $eventData[0]->colorid
						); 
						 
						$event_datetime = array( 
							'event_date' => $eventData[0]->dateoftrav,
							'event_end'  => $eventData[0]->dateoftrav_to,
							'start_time' => $eventData[0]->time_from, 
							'end_time' => $eventData[0]->time_to 
						); 

						// Get the access token 
						$access_token_sess = (isset($_SESSION['google_access_token']))?$_SESSION['google_access_token']:null; 
						if(!empty($access_token_sess)){ 
							$access_token = $access_token_sess; 
						}else{ 
							$data = $this->Calendarmodel->GetAccessToken($this->Calendarmodel->google_client_id, $this->Calendarmodel->redirect_uri, $this->Calendarmodel->google_client_secret, $_GET['code']); 
							$access_token = $data['access_token']; 
							$_SESSION['google_access_token'] = $access_token; 
						} 
						 
						if(!empty($access_token)){ 
							try { 
								// Get the user's calendar timezone 
								//echo $access_token; return;
								$user_timezone = $this->Calendarmodel->GetUserCalendarTimezone($access_token); 
								
								// Create an event on the primary calendar 
								//  $calendarid      = "merto.alvinjay@gmail.com"; // 
								//  $calendarid      = "primary"; // 
													 
													 // minda Calendar :: Alvin
								//$calendarid          = "c_2l7lk1n1jv12h02setje497bg4@group.calendar.google.com"; 
													
													 // minda calendar :: Mj
								$calendarid          = "c_5delohj7h4b5hebv2f7b40rlc4@group.calendar.google.com"; 
								
													// travel order calendar 
								// $calendarid          = "mindatravelorder@gmail.com"; 
								$google_event_id     = $this->Calendarmodel->CreateCalendarEvent($access_token, $calendarid, $calendar_event, 0, $event_datetime, $user_timezone); 
								
								//echo json_encode([ 'event_id' => $event_id ]); 
								if($google_event_id){ 
									// Update google event reference in the database 
								   // $sqlQ = "UPDATE events SET google_calendar_event_id=? WHERE id=?"; 
								   // $stmt = $db->prepare($sqlQ); 
									//$stmt->bind_param("si", $db_google_event_id, $db_event_id); 
									$db_google_event_id = $google_event_id; 
									$db_event_id = $event_id; 
									//$update = $stmt->execute(); 
									 
									unset($_SESSION['last_event_id']); 
									unset($_SESSION['google_access_token']); 
									 
									$status = 'success'; 
									$statusMsg = '<p>Event #'.$event_id.' has been added to Google Calendar successfully!</p>'; 
									$statusMsg .= "<p><a href='https://calendar.google.com/calendar/' target='_blank'>Open Calendar</a></p>";
									$statusMsg .= '<p><a href="https://office.minda.gov.ph:9003/my/travelorders">Go back to travel orders</a></p>';
									
									
								} 
							} catch(Exception $e) { 
								//header('Bad Request', true, 400); 
								//echo json_encode(array( 'error' => 1, 'message' => $e->getMessage() )); 
								$statusMsg = $e->getMessage(); 
							} 
						}else{ 
							$statusMsg = 'Failed to fetch access token!'; 
						} 
					}else{ 
						$statusMsg = 'Event data not found!'; 
					} 
				}else{
					$statusMsg = 'Event reference not found!'; 
				} 
				 
				$_SESSION['status_response'] = array('status' => $status, 'status_msg' => $statusMsg); 

				die("status: ".$statusMsg);
				//header("Location: addevent.php"); 
				//exit(); 
			} 
		}
	}

