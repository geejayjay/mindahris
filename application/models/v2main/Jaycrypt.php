<?php 
	
	class Jaycrypt extends CI_Model {
		public function now($word) {
			$pattern   = "abcdefghijklmnopqrstuvwxyz123456789";
			$enc 	   = "";
			
			for ($i = 0; $i <= strlen($word)-1; $i++) {
				if ($word[$i] == "0") {
					$enc .= ".";
				} else if ($word[$i] == "_") {
					$enc .= "@";
				}
			
				for($o = 0; $o <= strlen($pattern)-1; $o++) {
					if ($word[$i] == $pattern[$o]) {
						$index = $o-1;
						if ($o == 0) {
							$index = strlen($pattern)-1;
						}
						$enc .= $pattern[$index];
					} 
				}
			}
			return $enc;
		}
	}
