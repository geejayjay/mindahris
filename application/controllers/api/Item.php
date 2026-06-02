<?php 
	require APPPATH . 'libraries/REST_Controller.php';

	class Item extends REST_Controller {
	    public function __construct() {
		   parent::__construct();
		  //  $this->load->database();
			echo "hello";
		}
		
		public function index_get($id = 0)
		{
			$data = "hello world";
			if(!empty($id)){
				echo $id;
			}else{
				
			}
		 
			$this->response($data, REST_Controller::HTTP_OK);
		}
	}
