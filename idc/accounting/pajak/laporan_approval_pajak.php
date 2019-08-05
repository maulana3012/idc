<?php 
//REQUIRE
error_reporting(0);
require_once "../../zk_config.php";
require_once LIB_DIR ."zk_dbconn.php";
require_once "../../_script/PHPExcel.php";

// SQL STATEMENT

$_code	  = $_GET['_code'];
$_code	  = explode(",",trim($_code));
$_code   = "'".implode("','",$_code)."'";

$sql = "
SELECT
  bill_code,
  biit_idx,
  bill_vat_inv_no AS no_fp,
  to_char(bill_inv_date,'dd/Mon/YY') AS inv_date,
  bill_cus_to AS cus_code,
  bill_cus_to_name AS cus_name,
  it_model_no AS model_item,
  CASE 
  	WHEN '".ZKP_SQL."' = 'MED' AND bill_discount = 0.00 THEN 
		TRUNC ((biit_unit_price - ((biit_unit_price*bill_discount)/100)))
  	ELSE 
		TRUNC (biit_unit_price)
  END AS unit_price,
  
  TRUNC(biit_qty) AS qty,
  
  CASE 
  	WHEN '".ZKP_SQL."' = 'MED' AND bill_discount = 0.00 THEN 
		TRUNC ((biit_qty * (biit_unit_price - ((biit_unit_price*bill_discount)/100))))
  	ELSE 
		TRUNC ((biit_qty * biit_unit_price))
  END AS amount
FROM
  ".ZKP_SQL."_tb_billing AS a
  JOIN ".ZKP_SQL."_tb_billing_item AS b USING(bill_code)
WHERE bill_code IN ($_code)
ORDER BY bill_code,biit_idx
";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0	= array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['bill_code'],			//0
		$col['no_fp'],				//1
		$col['inv_date'],			//2
		$col['cus_code'], 			//3
		$col['cus_name'],			//4
		$col['model_item'], 		//5
		$col['unit_price'],			//6
		$col['qty'],				//7
		$col['amount']				//8
	);

	//1st grouping
	if($cache[0] != $col['bill_code']) {
		$cache[0] = $col['bill_code'];
		$group0[$col['bill_code']] = array();
	}

	if($cache[1] != $col['biit_idx']) {
		$cache[1] = $col['biit_idx'];
	}

	$group0[$col['bill_code']][$col['biit_idx']] = 1;
}

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();

// Set document properties
$objPHPExcel->getProperties()->setCreator("neki.arismi@gmail.com")
        ->setLastModifiedBy("neki.arismi@gmail.com")
        ->setTitle("Laporan Approval Faktur Pajak")
        ->setSubject("Laporan Approval Faktur Pajak")
        ->setDescription("Laporan Approval Faktur Pajak")
        ->setKeywords("laporan")
        ->setCategory("laporan");

// Setup Print
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setScale(85, true);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4); 
$sheet->getPageSetup()->setColumnsToRepeatAtLeftByStartAndEnd('A', 'B');
$sheet->getPageMargins()->setTop('.25');
$sheet->getPageMargins()->setBottom('.25');
$sheet->getPageMargins()->setLeft('.40');
$sheet->getPageMargins()->setRight('.25');

// Setup layout
$sheet->getSheetView()->setZoomScale(75);
$sheet->freezePane('A5');
$sheet->getColumnDimension('A')->setWidth(22);	// INVOICE NO & FP NO
$sheet->getColumnDimension('B')->setWidth(12);	// INVOICE DATE
$sheet->getColumnDimension('C')->setWidth(22);	// CUSTOMER NAME
$sheet->getColumnDimension('D')->setWidth(25);	// MODEL NO
$sheet->getColumnDimension('E')->setWidth(12);	// PRICE
$sheet->getColumnDimension('F')->setWidth(8);	// QTY
$sheet->getColumnDimension('G')->setWidth(15);	// AMOUNT
$objPHPExcel->setActiveSheetIndex(0);

// Title
$sheet->setCellValue('A1', "DAFTAR FAKTUR")
	  ->setCellValue('A2', "Generate at ".date('d/M/Y G:i'));

$sheet->getStyle("A1")->applyFromArray(array(
		'font' => array('size'=>14, 'bold'=>true)
   ));
 
$sheet->getStyle("A2")->applyFromArray(array(
		'font' => array('size'=>14, 'bold'=>true, 'italic'=>true)
   ));
   
// START THEAD
$sheet->setCellValue('A4', "INV.NO & FP.NO")
      ->setCellValue('B4', "INV.DATE")
	  ->setCellValue('C4', "CUS NAME")
	  ->setCellValue('D4', "MODEL NO")
	  ->setCellValue('E4', "UNIT PRICE")
	  ->setCellValue('F4', "QTY")
	  ->setCellValue('G4', "AMOUNT");

	  
// SHEET FORMAT	THEAD
$sheet->getStyle("A4:G4")->applyFromArray(array(
		'font' => array('size'=>14, 'bold'=>true, 'blue'=>true),
		'alignment' => array('wrap'=>true, 'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
		'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
   ));
 
$sheet->getStyle("A4:G4")->applyFromArray(array(
             'alignment' => array('wrap'=>true, 'horizontal'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
   ));
   
$total = array();
$i = 5;
foreach($group0 as $total1 => $group1) {
	$a = $i + count($group1) + 2;
	$sheet->setCellValue("A".$i, $rd[$rdIdx][0] ."\n". $rd[$rdIdx][1])
		  ->mergeCells("A".$i.":A".$a);						// INVOICE & FP NO
	$sheet->setCellValue("B".$i, $rd[$rdIdx][2])
		  ->mergeCells("B$i:B$a");							// INVOICE DATE
	$sheet->setCellValue("C".$i, "[". trim($rd[$rdIdx][3]) ."] ". $rd[$rdIdx][4])
		  ->mergeCells("C$i:C$a");							// CUSTOMER NAME

	$total['bill'] = array(0,0);
	foreach($group1 as $total2) {
		$sheet->setCellValue("D".$i, $rd[$rdIdx][5]);		// MODEL NO
		$sheet->setCellValue("E".$i, $rd[$rdIdx][6]);		// PRICE
		$sheet->setCellValue("F".$i, $rd[$rdIdx][7]);		// QTY
		$sheet->setCellValue("G".$i, $rd[$rdIdx][8]);		// AMOUNT
		
		$total["bill"][0] +=  $rd[$rdIdx][7];
		$total["bill"][1] +=  $rd[$rdIdx][8];
		$i++;
		$rdIdx++;
	}
	$sheet->setCellValue("D".$i, "BEFORE VAT")
		  ->mergeCells("D$i:E$i");
	$sheet->setCellValue("F".$i, $total["bill"][0]);
	$sheet->setCellValue("G".$i, $total["bill"][1]);
	$i++;
	
	$vat = ($total["bill"][1]*10)/100;
	$sheet->setCellValue("D".$i, "VAT")
		  ->mergeCells("D$i:E$i")
		  ->setCellValue("G".$i, $vat);
	$i++;
	
	$amount = $vat +  $total["bill"][1];
	$sheet->setCellValue("D".$i, "AMOUNT")
		  ->mergeCells("D$i:E$i")
		  ->setCellValue("G".$i, $amount);
	$i++;
}
$GPAGE = $i - 1;

$Reported = $GPAGE + 2;

$sheet->setCellValue("D".$Reported, "Reported By")
	  ->mergeCells("D$Reported:E$Reported")
	  ->getStyle("D$Reported:E$Reported")->applyFromArray(array(
             'alignment' => array('wrap'=>true, 'horizontal'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
	    ));	  
	  
$sheet->setCellValue("F".$Reported, "Approved By")
	  ->mergeCells("F$Reported:G$Reported")
	  ->getStyle("F$Reported:G$Reported")->applyFromArray(array(
             'alignment' => array('wrap'=>true, 'horizontal'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
	    ));

$sheet->getStyle("A5:C$GPAGE")->applyFromArray(array(
             'alignment' => array('wrap'=>true, 'horizontal'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
   ));
$sheet->getStyle("A5:C$GPAGE")->applyFromArray(array(
		'alignment' => array('wrap'=>true, 'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER),
   ));
$sheet->getStyle("A5:G$GPAGE")->applyFromArray(array(
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
   ));
$sheet->getStyle("A5:A$GPAGE")->applyFromArray(array(
			'font' => array('size'=>12, 'bold'=>true, 'blue'=>true),
   ));   
$sheet->getStyle("D5:G$GPAGE")->getNumberFormat()->setFormatCode('###,###,###,###,###');
  
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan"'.mktime().'".xlsx"');
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