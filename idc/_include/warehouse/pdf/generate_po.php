<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*/
//PAREPARE
require LIB_DIR . "fpdf/fpdi.php";

//Variable
$_po_date	= date("j-M-Y", strtotime($_po_date));
$_deli_date	= ($_deli_date!='') ? date("j-M-Y", strtotime($_deli_date)) : '';
if(ZKP_SQL == 'IDC')		$pdf_template = "idc_po_local.pdf";
else if(ZKP_SQL == 'MED')	$pdf_template = "med_po_local.pdf";

//Item list
$sql = "
SELECT
  it_code,
  it_model_no,
  it_desc,
  poit_qty,
  poit_unit,
  poit_unit_price, poit_unit_price * poit_qty AS amount,
  poit_remark
FROM
  ".ZKP_SQL."_tb_po_local_item
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
WHERE po_code = '$_code'
ORDER BY it_code
LIMIT 50
";
$result		=& query($sql);
$numRow		= numQueryRows($result);
$pdf = new FPDI();

//PO LOCAL ===============================================================================================
$counter = 0;
$total = array(0,0);
$row = array(26, $numRow);	//0.limit, 1.total item
$page = array(1, ceil($row[1] / $row[0]));
$no = 1;
while ($counter < $row[1]) {
	include "generate_po_detail.php";
	$page[0]++;
}

//PRINT END ==========================================================================================================
//detimine the document name & document path.
$storage	= PDF_STORAGE . "purchasing/po_local/". date("Ym/", strtotime($_po_date));
$doc_name	= $_code . "_rev_" . $_revision_time . ".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>