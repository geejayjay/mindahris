<?php 

	class Atts extends CI_Controller {
			
		public function show($exactid = '') {
			if ($exactid == '') { die("No files selected"); }
			
			$this->load->model("v2main/Globalproc");
			
			$details = $this->Globalproc->gdtf("checkexact",['exact_id'=>$exactid],"*");
			
			if (count($details)==0) { die("No files found"); }
			
			$attachments = (Array) json_decode($details[0]->attachments);
			
			foreach($attachments as $a) {
				echo "<img src='".base_url()."uploads/".$a."'/>";
				// echo "<img src='https://office.minda.gov.ph:9003/uploads/image.png_092519053141'/>";
			}
		}
	}

