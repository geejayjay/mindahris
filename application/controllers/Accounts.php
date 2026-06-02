<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class Accounts extends CI_Controller{
	

		public function __construct(){
			parent::__construct();
			// $this->load->library('session');
			$this->load->model('admin_model');
		}

		public function index(){
			$data['main_content'] = 'admin/dashboard';
			$this->load->view('login_view',$data);
		}

		public function login(){
			$is_debug = (getenv('DB_DEBUG') === 'true' || getenv('DB_DEBUG') === '1');

			if ($this->session->userdata('is_logged_in') == TRUE) {
				if ($is_debug) { log_message('debug', '[LOGIN] Already logged in, redirecting to base_url: ' . base_url()); }
				redirect(base_url(),"refresh");
			}
			
			$data['err'] = null;
			if (isset($_POST['loginbtn'])) {
				$info['username'] = $_POST['text'];
				$info['password'] = md5($_POST['password']);

				$this->load->model("login_model");
				
				if ($is_debug) { log_message('debug', '[LOGIN] Step 1: Calling authorizeUser for username: ' . $info['username']); }
				$query = $this->login_model->authorizeUser($info);

				if ($is_debug) { log_message('debug', '[LOGIN] Step 2: authorizeUser returned ' . count($query) . ' result(s)'); }

				if (count($query)==0) {
					$data['err']	 = "<p style='margin: 0px; text-align: center; background: #fcc8c8; padding: 11px;'> User not recognized. </p> ";
					if ($is_debug) { log_message('debug', '[LOGIN] Step 2b: User not recognized'); }
				} else {
					if ($is_debug) {
						log_message('debug', '[LOGIN] Step 3: User found. Column names from authorizeUser: ' . implode(', ', array_keys((array) $query[0])));
						log_message('debug', '[LOGIN] Step 3b: username value = ' . ($query[0]->username ?? 'NULL'));
					}

					$DB2 = $this->load->database('sqlserver', TRUE);

					if ($is_debug) {
						log_message('debug', '[LOGIN] Step 4: DB connection object type: ' . (is_object($DB2) ? get_class($DB2) : gettype($DB2)));
						log_message('debug', '[LOGIN] Step 4b: DB conn_id: ' . ($DB2->conn_id ? 'valid' : 'FALSE'));
						log_message('debug', '[LOGIN] Step 4c: DB driver: ' . $DB2->dbdriver);
						log_message('debug', '[LOGIN] Step 4d: DSN: ' . $DB2->dsn);
					}

					$sql = "select 
						u.Username,
						u.usertype,
						e.*,
						d.Division_Desc as office_division_name,
						p.position_name,
						a.area_name
						from Users as u 
						LEFT JOIN employees as e 
							on u.employee_id = e.employee_id
						LEFT JOIN areas as a 
							on e.area_id = a.area_id
						LEFT JOIN Division as d 
							on e.Division_id = d.Division_Id 
						LEFT JOIN positions as p 
							on e.position_id = p.position_id
						where u.Username = '{$query[0]->username}' and u.Password = '{$info['password']}'";

					if ($is_debug) { log_message('debug', '[LOGIN] Step 5: Executing second query'); }
					$q = $DB2->query($sql);

					if ($q === FALSE) {
						if ($is_debug) { log_message('debug', '[LOGIN] Step 5b: Second query FAILED (returned FALSE). DB error: ' . json_encode($DB2->error())); }
						$data['err'] = "<p style='margin: 0px; text-align: center; background: #fcc8c8; padding: 11px;'> Login failed. Please try again. </p>";
					} else {
						$query = $q->result();
						if ($is_debug) {
							log_message('debug', '[LOGIN] Step 6: Second query returned ' . count($query) . ' result(s)');
							if (count($query) > 0) {
								log_message('debug', '[LOGIN] Step 6b: Column names: ' . implode(', ', array_keys((array) $query[0])));
							}
						}

						if (count($query) == 0) {
							if ($is_debug) { log_message('debug', '[LOGIN] Step 6c: Second query returned 0 results'); }
							$data['err'] = "<p style='margin: 0px; text-align: center; background: #fcc8c8; padding: 11px;'> Login failed. Please try again. </p>";
						} else {
							// Normalize column names to lowercase for cross-platform compatibility
							// (FreeTDS/pdo_dblib on Linux lowercases all column names, mssql on Windows preserves case)
							$row = (object) array_change_key_case((array) $query[0], CASE_LOWER);

							if ($is_debug) {
								log_message('debug', '[LOGIN] Step 7: Normalized column names: ' . implode(', ', array_keys((array) $row)));
								log_message('debug', '[LOGIN] Step 7b: employee_id=' . ($row->employee_id ?? 'NULL') . ', username=' . ($row->username ?? 'NULL'));
							}

							// create session
							// ==============================================================================
								$this->session->set_userdata('employee_id', $row->employee_id);
								$this->session->set_userdata('username', $row->username);
								$this->session->set_userdata('usertype', $row->usertype);
								$this->session->set_userdata('full_name', $row->f_name);
								$this->session->set_userdata('first_name', $row->firstname);
								$this->session->set_userdata('last_name', $row->l_name);
								$this->session->set_userdata('biometric_id', $row->biometric_id);
								$this->session->set_userdata('area_id', $row->area_id);
								$this->session->set_userdata('area_name', $row->area_name);
								$this->session->set_userdata('ip_address', $_SERVER["REMOTE_ADDR"]);
								$this->session->set_userdata('is_logged_in', TRUE);
								$this->session->set_userdata('database_default', 'sqlserver');
								$this->session->set_userdata('employment_type', $row->employment_type);
								$this->session->set_userdata('employee_image', $row->employee_image);
								$this->session->set_userdata('level_sub_pap_div', $row->level_sub_pap_div);
								$this->session->set_userdata('division_id', $row->division_id);
								$this->session->set_userdata('dbm_sub_pap_id', $row->dbm_pap_id);
								$this->session->set_userdata('is_head', $row->is_head);
								$this->session->set_userdata('office_division_name', $row->office_division_name);
								$this->session->set_userdata('position_name', $row->position_name);
								$this->session->set_userdata('isfocal', $row->isfocal);
							// ==============================================================================
							// end creation of session

							if ($is_debug) {
								log_message('debug', '[LOGIN] Step 8: Session data set. is_logged_in = ' . ($this->session->userdata('is_logged_in') ? 'TRUE' : 'FALSE'));
								log_message('debug', '[LOGIN] Step 8b: Session ID = ' . session_id());
								log_message('debug', '[LOGIN] Step 8c: Redirecting to: ' . base_url());
							}

							redirect(base_url(),"refresh");
						}
					}
				}
			}

	      	$data['main_content'] = 'login_view';
			$this->load->view('hrmis/login_view',$data);
      
		}

		public function logout(){

			$this->session->unset_userdata('is_logged_in');
	      	$this->session->unset_userdata('employee_id');
		    $this->session->unset_userdata('username');
		    $this->session->unset_userdata('usertype');
		    $this->session->unset_userdata('full_name');

		    $this->session->unset_userdata('area_id');
		    $this->session->unset_userdata('ip_address');
		    $this->session->unset_userdata('area_name');
		    $this->session->unset_userdata('database_default');

		    $this->session->sess_destroy();

			redirect('/accounts/login/', 'refresh');
		}
		
		public function checklogin() {
			if ($this->session->userdata('is_logged_in') == TRUE) {
				echo json_encode(true);
			} else {
				echo json_encode(false);
			}
		}
		
		public function test() {
			// session_start();
			$_SESSION['sample'] = "sample";
			
			redirect(base_url()."accounts/here","refresh");
			
		}
		
		public function here() {
			echo $_SESSION['sample'];
		}

}