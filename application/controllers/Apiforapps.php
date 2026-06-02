<?php 

	class Apiforapps extends CI_Controller{
		public function employees() {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$this->load->database("sqlserver", TRUE);
			
			$sels  	 = $_GET['field'];
			$where 	 = $_GET['where'];
			
			$sels_ar = explode(",",$sels);
			
			$sql   = "select ";
			$count = 0;
			foreach($sels_ar as $sa) {
				$nsa = trim(trim(trim($sa,'\"'),'['),']');
				$sql .= $nsa;
				$sql .= ($count==count($sels_ar)-1)?"":",";
				$count++;
			}
			
			/*
			$count = 0;
			foreach($qs as $q) {
				$sql .= $q;
				$sql .= ($count == count($qs))?"":",";
			}
			*/
			
			echo "<br/>";
			echo $sql;
			// $q   = $this->db->query($sql);
			// $ret = $q->result();
			
		}
	}

