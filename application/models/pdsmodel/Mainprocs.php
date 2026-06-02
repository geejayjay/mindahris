<?php 

	class Mainprocs extends CI_Model {
		public $selecteddb = "pdsdb";
		// public $selecteddb = "sqlserver";
	
		public function get_pidn() {
			$user_id = $this->session->userdata('employee_id');
			return $user_id;
		}
		
		public function checkforinput() {
			$userid = $this->get_pidn();
			
			$has = $this->fetch("personalinformation",['pidn'=>$userid],['pidn']);
			
			if (count($has) == 0) {
				return false;
			}
			
			return true;
		}
		
		public function __getdata($sql) { //, $table = false
			$this->load->database($this->selecteddb, TRUE);

			//$session_database = "default";
			//$this->load->database($session_database, TRUE);

			$ret = null;

			if (!is_array($sql)) {
				$ret = $this->db->query($sql);
			} else {
				// write a query for values sent in array
				// for the meantime run code in sql form
			}

			$ret = $ret->result();
			$this->db->close();
			return $ret;
			
		}

		public function fetch($table, $where = false, $details , $orderby = false) {
			//$this->load->database($this->selecteddb, TRUE);
			$sql = "SELECT ";
			
			if (is_array($details)){
				$count = 0;
				foreach($details as $d) {
					$sql .= $d;
					$sql .= (count($details)-1==$count)?"":",";
					$count++;
				}
			} else {
				$sql .= $details;
			}
			
			$sql .= " FROM ".$table;

			if ($where != false) {
				$sql .= " WHERE ";
				if (is_array($where)){
					$count = 0;
					foreach($where as $key => $val) {
						if ($key == "conn") {
							$sql .= " ".$val." ";
						} else {
							$sql .= $key."='".$val."'";
						}
					}
				} else {
					$sql .= " ".$where." ";
				}	
			}

			if ($orderby != false) {
				$sql .= " ORDER BY {$orderby}";
			}
			
			$var = $this->__getdata($sql);
			
			return $var;
		}

		public function __store($table, $values) {
			$this->load->database($this->selecteddb, TRUE);

			$sql = null;
			if (is_array($values)) {
				$sql = "insert into {$table} (";
				$count = 0;
				foreach (array_keys($values) as $ks) {
					$sql .= $ks;
					$sql .= ($count == count($values)-1) ? "" : ", ";
					$count++;
				}
				$sql .= ") values (";

				$count = 0;
				foreach($values as $key => $vals) {
					$sql .= "'".$vals."'";
					// $sql .= $this->db->escape($vals);
					$sql .= ($count == count($values)-1)? "": ",";
					$count++;
				}
				$sql .= ")";
			}

			$ret = $this->db->query($sql);
			$this->db->close();
			return $ret;
		}

		public function __update($table, $values, $where) {
			$this->load->database($this->selecteddb, TRUE);
		
			$sql = null;

			if (is_array($values)) {
				$count = 0;
				$sql = "update {$table} set ";

				foreach($values as $key => $vals) {
					$sql .= $key."='".$vals."'";
					$sql .= ($count==count($values)-1)?"":", ";
					$count++;
				}

				$count = 0;
				// conn = and 
				// conn = or
				if (is_array($where)){
					$sql .= " where ";
					foreach($where as $key => $val) {
						if ($key == "conn") {
							$sql .= " ".$val." ";
						} else {
							$sql .= $key."='".$val."'";	
						}
					}
				}
			
				$ret = $this->db->query($sql);
				$this->db->close();
				return $ret;
			}

			return false;

		}

		public function getrecent($table, $callback) {
			$this->load->database($this->selecteddb, TRUE);
			$sql = "SELECT IDENT_CURRENT('".$table."') as ".$callback;  
			$ret = $this->db->query($sql)->result();
			$this->db->close();
			return $ret;
		}

		public function __runsql($sql) {
			$this->load->database($this->selecteddb, TRUE);
			$ret = $this->db->query($sql);
			$this->db->close();
			return $ret;
		}

		public function tokenizer($word, $length = false) {
			$length = ($length == false)?"11":$length;
			return substr(md5(substr(md5($word),0,$length)),0,$length);
		}
	
		public function getinformation($table) {
			
			$where = ["pidn" => $this->get_pidn()];
			$data  = $this->fetch($table,$where,["*"]);

			return $data;
		}
		
		public function makenewentry($tbl, $details = false) {
			$id  = $this->get_pidn();
			
			if ($details != false) {
				$new = $this->__store($tbl,['pidn' => $id]);
			} else {
				$new = $this->__store($tbl, $details);
			}
			
			return $new;
		}
	}

