<?php 

	class Pdsajax extends CI_Controller {
		function saveinput() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$data = $_GET['d'];
			
			//{field: "surname", value: "thisisthevalue"}
			$id 	 = $this->Mainprocs->get_pidn();
			
			$tbl     = $data['boxbelong'];
			$details = [$data['field'] => htmlspecialchars($data['value'],ENT_QUOTES)];
			
			$where   = null;
			
			if ( isset($data['isaddr']) ) {
				$where   = ["pidn" => $id, "conn"=>"and",$data['addrinfo']=>$data['fldval']];
			} else if ( isset($data['extra']) ) {
				$where   = ["pidn" => $id, "conn"=>"and",$data['xfld']=>$data['xfldval']];
			} else {
				$where   = ["pidn" => $id];
			}
			
			if ($where == null) return; 
			
			$update_ = $this->Mainprocs->__update($tbl,$details,$where);
			echo json_encode($update_);
		}
		
		function savenew() {
			$this->load->model("pdsmodel/Mainprocs");
			$data  = $_GET['d'];
			
			$id     = $this->Mainprocs->get_pidn();
			$tbl    = $data['boxbelong'];
			$where  = ["pidn" => $id, "conn" => "and", $data['field'] => $data['value']];
			$insert = ['pidn' => $id,$data['field']=> htmlspecialchars($data['value'],ENT_QUOTES)];
			
			$hasdata = $this->Mainprocs->fetch($tbl,$where,[$data['field']]);
			
			$return = null;
			
			if ($hasdata == null) {
				// create new 
				$return = $this->Mainprocs->__store($tbl,$insert);
			} else {
				$return = $hasdata;
			}
			
			echo json_encode($return);
		}
		
		function appendasnew() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$d    = $_GET['d'];
			$id   = $this->Mainprocs->get_pidn();
			
			$tbl  = $d['boxbelong'];
			$vals = $d['vals'];
			
			$save = null;
			
			$details['pidn'] = $id;
			foreach($vals as $obs) {		
				foreach($obs as $key => $vals) {
					$details[$key] =  htmlspecialchars($vals,ENT_QUOTES);
				}
				$save = $this->Mainprocs->__store( $tbl , $details );
			}
			
			echo json_encode($save);
		}
		
		function updateexisting() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$d  		   = $_GET['d'];
			// $id    		   = $this->Mainprocs->get_pidn();
			
			$tbl   		   = $d['boxbelong'];
			$vals  		   = $d['vals'];
			
			$where[$d['fld']] = $d['listid'];
			$details 	   	  = [];
			$save 		   	  = null;
			
			foreach($vals as $obs) {
				foreach($obs as $key => $valss) {
					$details[$key]	= htmlspecialchars($valss,ENT_QUOTES);
				}
				$save = $this->Mainprocs->__update($tbl, $details, $where);
			}
			echo json_encode($save);
		}
		
		function getdata() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$id 	  = $_GET['id'];
			$idfield  = $_GET['idfld'];
			$tbl 	  = $_GET['tbl'];
			
			$data 	  = $this->Mainprocs->fetch($tbl,[$idfield => $id],["*"]);

			echo json_encode($data);
		}
		
		function insertupdate_q() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$d  = $_GET['d'];
			$id = $this->Mainprocs->get_pidn();
			
			$tbl = $d['boxbelong'];
			
			$fld = $d['fld'];
			$val = $d['val'];
			
			$check = $this->Mainprocs->fetch($tbl,["fld"=>$fld,"conn"=>"and","pidn" => $id],["*"]);
			
			$save = null;
			if (count($check) == 0) {
				// insert	
				$save = $this->Mainprocs->__store($tbl,["fld" => $fld,"val_u"=>$val,"pidn"=>$id]);
			} else {
				// update 
				$save = $this->Mainprocs->__update($tbl,["val_u"=>$val],["pidn"=>$id,"conn"=>"and","fld"=>$fld]);
			}
			echo json_encode($save);
		}
		
		function createpds() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$id 	 = $this->Mainprocs->get_pidn();
			
			$details = ["pidn" => $id];
			$saved 	 = $this->Mainprocs->__store("personalinformation",$details);
			
			if ($saved) {
				echo json_encode("true");
			} else {
				echo json_encode("false");
			}
		}
		
		function opencomponent() {
			$this->load->model("pdsmodel/Mainprocs");
			$view 	 = $_POST['view'];
			$value   = $_POST['theval'];
			$tbl     = $_POST['tbl_'];
			$field   = $_POST['field'];
			
			$id 	 = $this->Mainprocs->get_pidn();
			
			$where   	   = [$field => $value,"conn"=>"and","pidn"=>$id];
			
		// 	var_dump($where);
			
			$orderby = false;
			if ($tbl == "seminars" || $tbl == "workexp") {
				$orderby = "from_ DESC";
			}
			
			$fetch['data'] = $this->Mainprocs->fetch($tbl,$where,["*"],$orderby);
			$fetch['adds'] = [$value,$tbl];
			
			$this->load->view($view,$fetch);
		}
		
		function deleteitem() {
			$this->load->model("pdsmodel/Mainprocs");
			$id = $this->Mainprocs->get_pidn();
			
			$data  = $_GET['d'];
			$tbl   = $data['boxbelong'];
			$fld   = $data['field'];
			$val   = $data['value'];
			
			$sql   = "delete from {$tbl} where {$fld} = '{$val}'";
			$delete = $this->Mainprocs->__runsql($sql);
			
			echo json_encode($delete);
		}
		
		function checklogin() {
			$this->load->model("pdsmodel/Mainprocs");
			$id 	 = $this->Mainprocs->get_pidn();
			echo json_encode($id);
		}
		
		function offclick() {
			$this->load->model("v2main/Globalproc");
			
			$offid = $_GET['offid'];
			$data  = $this->Globalproc->gdtf("Division",["DBM_Sub_Pap_Id"=>$offid],"*");
			
			$a = $this->load->view("pds/pdsadmincontents/officeclick.php",["d"=>$data,"offid"=>$offid],true);
			echo $a;
		}
		
		function divclick() {
			$this->load->model("v2main/Globalproc");
			
			//  and is_head='0'
		
			$showwhat = $_GET['showwhat'];
			$filter   = false;
			switch($showwhat) {
				case "raf":
					$filter = " and is_head = '0'";
					break;
				case "cau":
					$filter = " and is_head = '1'";
					break;
			}
			
			$divid = $_GET['divid'];
			
			$sql = "select * from employees where Division_id ='{$divid}' and status ='1' and employment_type='regular' {$filter}";
		
			if ($_GET['offlvl']=="true"){
				$sql = "select * from employees where DBM_Pap_id='{$divid}' and Division_id = '0' and status='1' and employment_type='regular' {$filter}";
			}
			
			$data = $this->Globalproc->__getdata($sql);
			
			$a = $this->load->view("pds/pdsadmincontents/empdisplay",["emps"=>$data],true);
			
			echo $a;
		}
		
		function empseminars() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$empid 			= $_GET['pidn'];
			
			$fiveyearstoday = null;
			$today 			= null;
			
			if ($_GET['incdates']== "false") {
				$fiveyearstoday = date("m/d/Y", strtotime("-5 years"));
				$today 			= date("m/d/Y");
			} else {
				list($from, $to) = explode("_",$_GET['incdates']);
				
				$fiveyearstoday = date("m/d/Y", strtotime($from));
				$today 		    = date("m/d/Y", strtotime($to));
			}
			
			$filter = false;
			if ($_GET['showwhat'] != "All") {
				$filter = " and typeofsem = '{$_GET['showwhat']}'";
			}
			
			$sql = "select * from (select * from pdsdb.dbo.seminars where to_ between '{$fiveyearstoday}' and '{$today}') as tb1 where tb1.pidn ='{$empid}' {$filter} order by from_ DESC";

			if ($_GET['emptype']=="cau"){
				$sql = "select * from pdsdb.dbo.seminars where pidn ='{$empid}' {$filter} order by from_ DESC";
			}
		
			$samp = ['ebgrnd'=>null,"oskil"=>null];
			
				// get data based from the ticked checkboxes in the additional filter area 
					$filters = $_GET['addfil'];

					if ($filters['ebgrnd'] == "true") {
						$samp['ebgrnd'] = $this->Mainprocs->fetch("pdsdb.dbo.educbg",['pidn'=>$empid],"*");
					}
					
					if ($filters['oskil'] == "true") {
						$samp['oskil'] = $this->Mainprocs->fetch("pdsdb.dbo.otherinfo",["pidn"=>$empid, "conn"=>"and","typeofoi"=>"ssh"],"*");
					}
 				// ====================== end 
			
			$this->Mainprocs->selecteddb = "pdsdb";
			$data = $this->Mainprocs->__getdata($sql);
				
			$a = $this->load->view("pds/pdsadmincontents/empseminars",["seminars"=>$data,"afilters"=>$samp], true);
			echo $a;
		}
		
		function resultdiv() {
			$a = $this->load->view("pds/pdsadmincontents/theresult",0, true);
			echo $a;
		}
		
		function editchildren() {
			$this->load->model("pdsmodel/Mainprocs");
			
			$cid  	  = $_POST['cid'];
			
			//$children = $this->Mainprocs->__getdata(
		}
	}

