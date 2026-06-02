<?php
	require APPPATH."libraries/vendor/autoload.php";
	
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	
	class Toexcel extends CI_controller {
		
		public function inder() {
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			$spreadsheet = $reader->load('assets/images/pds/csc_pdf.xls');
		 
			$sheet = $spreadsheet->getActiveSheet();
			$last_row = (int) $sheet->getHighestRow();
			$new_row = $last_row+1;
		 
			$sheet->setCellValue('A'.$new_row, “14”);
			$sheet->setCellValue('B'.$new_row, “Alina”);
			$sheet->setCellValue('C'.$new_row, “PG”);
			$sheet->setCellValue('D'.$new_row, “32”);
			$sheet->setCellValue('E'.$new_row, “Pending”);
		 
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
			$writer->save('Hostel_records.xlsx');
		}
	}
