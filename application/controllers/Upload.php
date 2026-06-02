<?php
	
	class Upload extends CI_Controller {
		
		public function file() {
			$this->load->view("v2views/upload");
		}
		
		function beginupload() {
			$target_dir 	= FCPATH."uploads/";
			$basename       = date("mdyHis")."_".basename($_FILES["file"]["name"]);
			$target_file 	= $target_dir . $basename;
			$uploadOk 		= 1;
			$imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			$msg 			= null;
			// Check if image file is a actual image or fake image
			// if(isset($_POST["submit"])) {
				$check = getimagesize($_FILES["file"]["tmp_name"]);
				if($check !== false) {
					$msg = "<span style='color:green;'> File is an image - " . $check["mime"] . ". </span>";
					$uploadOk = 1;
				} else {
					$msg = "<span style='color:red;'>File is not an image.</span>";
					$uploadOk = 0;
				}
			// }
			
			// $free = 0;
			// do {	
				// Check if file already exists
			// $target_file = $target_file."_".date("mdyHis").$imageFileType;
			//	$target_file .= $imageFileType;
				if (file_exists($target_file)) {
					$msg = "<span style='color:red;'>Sorry, file already exist. Try renaming the file and upload again.</span>";
					$uploadOk = 0;
				}
			/*
			else {
					$free = 1;
				}
			} while ($free == 0)
			*/
			// Check file size
			if ($_FILES["file"]["size"] > 50000000) {
				$msg = "<span style='color:red;'>Sorry, your file is too large.</span>";
				$uploadOk = 0;
			}
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" ) {
				$msg = "<span style='color:red;'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</span>";
				$uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				//$msg = "<span style='color:red;'>Sorry, your file was not uploaded.</span>";
			// if everything is ok, try to upload file
			} else {
				if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
					$msg = "<span style='color:green;'>The file ". basename( $_FILES["file"]["name"] ). " has been uploaded.</span>";
				} else {
					$msg = "<span style='color:red;'>Sorry, there was an error uploading your file.</span>";
				}
			}
			// basename($_FILES["file"]["name"])
			echo json_encode( Array("msg"=>$msg,"filename"=>$basename,"isok"=>$uploadOk )); // basename($_FILES["file"]["name"])
		}
		
		function test() {
			var_dump( @$_SERVER["SCRIPT_NAME"] );
		}
	}
