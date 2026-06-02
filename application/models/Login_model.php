<?php

Class Login_model extends CI_Model{



    public function __construct(){
      parent::__construct();
      $this->load->model('main/main_model');

    }
	
	

    function authorizeUser($info){

            $DB2 = $this->load->database('sqlserver', TRUE);

            $this->load->library('encrypt');
            $username =  $this->main_model->encode_special_characters($info['username']);
            $password = $info['password'];


            $query = "SELECT 1 as success , u.employee_id as employee_id , u.username , u.usertype, u.isfirsttime FROM users u
                      WHERE u.username = '{$username}' AND u.password = '{$password}'
                      ";

            $query = $DB2->query($query);
			
			if ($query === FALSE || count($query->result())==0) {
				$q = "SELECT 1 as success , u.employee_id as employee_id , u.username , u.usertype, u.isfirsttime FROM users u
                      WHERE u.e_add like '%{$username}%' AND u.password = '{$password}'
                      ";
					  
				$query = $DB2->query($q);
			}
			
			if ($query === FALSE) {
				return array();
			}
			
            $result = $this->main_model->array_utf8_encode_recursive($query->result());
			
			if (count($result)>=1) {
				$firsttime_val = $result[0]->isfirsttime;
				
				if ($firsttime_val != 0) {
					if ($firsttime_val == 1) {
						$firsttime_val = 1;
					} else {
						$firsttime_val += 1;
					}
					
					$u  = "update users set isfirsttime = '{$firsttime_val}' where employee_id = '{$result[0]->employee_id}' and password = '{$password}'";
					$qe = $DB2->query($u);
				}
			}
			
            return $result;
    
    }
  
    function getUserInformation($employee_id){

            $DB2 = $this->load->database('sqlserver', TRUE);

            $query = "SELECT e.* , a.area_name, p.position_name , 
                          CASE e.Level_sub_pap_div
                          WHEN 'Division' THEN d.Division_Desc
                          WHEN 'DBM_Sub_Pap' THEN dsp.DBM_Sub_Pap_Desc
                          END as office_division_name 
                      FROM employees e
                      LEFT JOIN areas a ON a.area_id = e.area_id
                      LEFT JOIN Division d ON e.Division_id = d.Division_Id
                      LEFT JOIN DBM_Sub_Pap dsp ON e.DBM_Pap_id = dsp.DBM_Sub_Pap_id
                      LEFT JOIN positions p ON p.position_id = e.position_id
                      WHERE e.employee_id = '{$employee_id}';
                      ";

          $query = $DB2->query($query);

          if ($query === FALSE) {
              return array();
          }

          $result = $this->main_model->array_utf8_encode_recursive($query->result());
          return $result;

    }
    

}


