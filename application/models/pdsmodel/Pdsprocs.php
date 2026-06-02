<?php 
	class Pdsprocs extends CI_Model {
		public $db = "pdsdb";
		
		public function save() {
			$this->load->database($this->db,TRUE);
			
		}
	}
