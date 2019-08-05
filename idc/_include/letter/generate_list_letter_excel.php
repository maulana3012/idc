<?php 
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR ."zk_dbconn.php";
require_once "../../_script/PHPExcel.php";


$status	= array(1=>"ON PROCESS", "CONFIRMED", "CANCELLED");
$type = array("T"=>"TENDER", "Q"=>"QUOTATION", "B"=>"BUSINESS", "O"=>"OTHERS");
$_module		= isset($_GET['_module'])? $_GET['_module'] : "";
$_dept		 	= isset($_GET['cboDept'])? $_GET['cboDept'] : "";
$_status	 	= isset($_GET['cboStatus'])? $_GET['cboStatus'] : "";
$_type	 		= isset($_GET['cboType'])? $_GET['cboType'] : "";
$_is_file_exist	= isset($_GET['cboFileExist'])? $_GET['cboFileExist'] : "";
$cboSearchBy	= isset($_GET['cboSearchBy'])? $_GET['cboSearchBy'] : "";
$txtSearchBy	= isset($_GET['txtSearchBy'])? $_GET['txtSearchBy'] : "";
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());


if($_status != "") {
	$tmp[] = "lt_status_of_letter = '$_status'";
}

if($_type != "") {
	$tmp[] = "lt_type_of_letter = '$_type'";
}

if($_is_file_exist != "") {
	$tmp[] = ZKP_SQL."_isFileExist(lt_reg_no) = $_is_file_exist";
}

if($cboSearchBy != "" && $txtSearchBy != "") {
	$tmp[] = $search_by[$cboSearchBy] . " ILIKE '%$txtSearchBy%'";
}

if($_module != "summary") {
	$tmp[] = "lt_dept = '".strtoupper(substr($_module,0,1))."'";	
} else {
	if($_dept != "") {
		$tmp[] = "lt_dept = '$_dept'";
	}
}
$tmp[] = "lt_reg_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
$strWhere = implode(" AND ", $tmp);

$sql = "SELECT * FROM ".ZKP_SQL."_tb_letter WHERE $strWhere ORDER BY lt_reg_date DESC";
/*
echo "<pre>";
var_dump($sql, $_GET);
echo "</pre>";
exit;
*/
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("neki.arismi@gmail.com")
        ->setLastModifiedBy("neki.arismi@gmail.com")
        ->setTitle("Daftar Nomor Surat")
        ->setSubject("Daftar Nomor Surat")
        ->setDescription("Daftar Nomor Surat")
        ->setKeywords("laporan")
        ->setCategory("laporan");

// Setup Print
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5); 
$sheet->getPageSetup()->setColumnsToRepeatAtLeftByStartAndEnd('A', 'B');
$sheet->getPageMargins()->setTop('.25');
$sheet->getPageMargins()->setBottom('.25');
$sheet->getPageMargins()->setLeft('.25');
$sheet->getPageMargins()->setRight('.25');

// Setup layout
$sheet->getSheetView()->setZoomScale(80);
$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(17);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(30);
$sheet->getColumnDimension('F')->setWidth(60);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(50);
$sheet->getColumnDimension('J')->setWidth(15);
$objPHPExcel->setActiveSheetIndex(0);

// START THEAD
$sheet->setCellValue('A3', "NO")
      ->setCellValue('B3', "LETTER NO")
      ->setCellValue('C3', "DATE")
      ->setCellValue('D3', "TYPE")
      ->setCellValue('E3', "SEND TO")
      ->setCellValue('F3', "BRIEF SUMMARY")      
      ->setCellValue('G3', "ITEM REGISTERED")
      ->setCellValue('H3', "PIC")
      ->setCellValue('I3', "ADDRESS")
      ->setCellValue('J3', "STATUS")
      ;


// START TBODY
$i = 1;
$r = 4;
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$sheet->setCellValue( "A".$r,  $i)
		  ->setCellValue( "B".$r,  $col["lt_reg_no"])
		  ->setCellValue( "C".$r,  date("d-M-y", strtotime($col["lt_reg_date"])))
		  ->setCellValue( "D".$r,  $type[$col['lt_type_of_letter']])
      ->setCellValue( "E".$r,  $col['lt_send_to'])
		  ->setCellValue( "F".$r,  html_entity_decode($col['lt_brief_summary']))
		  ->setCellValue( "G".$r,  $col['lt_item'])
		  ->setCellValue( "H".$r,  $col['lt_pic'])
		  ->setCellValue( "I".$r,  $col['lt_address'])
		  ->setCellValue( "J".$r,  $status[$col["lt_status_of_letter"]])
		  ;

	$i++;
	$r++;

}

$r--;


//Sheet Format
$sheet->getStyle("A3:"."J$r")->applyFromArray(array(
             'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
   ));

$sheet->getStyle("A3:J$r")->applyFromArray(array(
             'alignment' => array('wrap'=>true, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
   ));

$sheet->getStyle("A3:J$r")->applyFromArray(array(
             'alignment' => array('wrap'=>true, 'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
   ));



// Rename worksheet
$sheet->setTitle("Sheet1");

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Data Nomer Surat "'.mktime().'".xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
