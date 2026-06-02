<?php 
	
	class Onlinedirectory extends CI_Controller {

		public function index() {
			redirect(base_url()."onlinedirectory/minda/pppdo",'refresh');
		}

		public function minda($office = false, $division = false) {
			$this->load->model("oldirmodel/Procsoldir");

			$data['office']	  = $this->Procsoldir->office = $office;
		
			/*
			if (isset($_POST['filterbtn'])) {
				if (!empty($_POST['fullname'])) {
					$this->Procsoldir->name = $_POST['fullname'];
				}

				if (!empty($_POST['contactno'])) {
					$this->Procsoldir->contactnumber = $_POST['contactno'];
				}

				if (!empty($_POST['training'])) {
					$this->Procsoldir->training = $_POST['training'];
				}

				if (!empty($_POST['course'])) {
					$this->Procsoldir->coursedegree = $_POST['course'];
				}

				if (!empty($_POST['skills'])) {
					$this->Procsoldir->expertiseskills = $_POST['skills'];
				}
			}
			*/
			if (isset($_POST['searchtext'])) {
				// searchcat : for the category
				//  $chrono
				$this->Procsoldir->category  = $_POST['searchcat'];
				$this->Procsoldir->thesearch = $_POST['searchtext'];
				if (isset($_POST['anyoftheword'])) {
					$this->Procsoldir->chrono = true;
				}
			}

			if ($division != false) {
				$data['sel_div'] = $this->Procsoldir->division = $division;
			}

			$data['division'] = $this->Procsoldir->getdivision();
			$data['emps']     = $this->Procsoldir->displayemps();
			
			if (isset($_GET['show']) && $_GET['show'] == "summary") {
				$data['summary'] = $this->Procsoldir->summary($data['emps']);
			}
			
			$this->load->view("oldir/displayoldircontent",["data"=>$data]);
		}
		
		public function summary() {
			
		}
	}

