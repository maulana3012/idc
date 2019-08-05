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

$_po_date		= date("j-M-Y", strtotime($_po_date));
$_revision_time += 1;

if($_layout_type == 1) 		$pdf_template = strtolower(ZKP_URL)."_po_form_1.pdf";
else if($_layout_type == 2) $pdf_template = strtolower(ZKP_URL)."_po_form_2.pdf";
else if($_layout_type == 3) $pdf_template = strtolower(ZKP_URL)."_po_form_3.pdf";
else if($_layout_type == 4) $pdf_template = strtolower(ZKP_URL)."_po_form_4.pdf";

$curr = array(1=>'US $',2=>'Rp');

//Supplier info
$info_sql	= "SELECT sp_phone, sp_fax, sp_contact_attn, sp_contact_cc FROM ".ZKP_SQL."_tb_supplier WHERE sp_code='$_sp_code'";
$info_res	=& query($info_sql);
$info		=& fetchRow($info_res);

//Item list
$sql = "
SELECT
  it_code,			--0
  poit_item,		--1
  poit_desc,		--2
  poit_unit_price,	--3
  poit_qty,			--4
  poit_unit_price * poit_qty AS amount,		--5
  poit_attribute,	--6
  poit_remark		--7
FROM
  ".ZKP_SQL."_tb_po_item
WHERE po_code = '$_code'
ORDER BY it_code
LIMIT 30
";
$result =& query($sql);
$numRow = numQueryRows($result);
$pdf = new FPDI();

//PO ===============================================================================================
$counter = 0;
$total = array(0,0);
$offsetRow	= array(1=>19,19,19,19);
$row = array($offsetRow[$_layout_type], $numRow);	//0.limit, 1.total item
$page = array(1, ceil($row[1] / $row[0]));
$no = 1;
while ($counter < $row[1]) {
	include "generate_layout_". $_layout_type .".php";
	$page[0]++;
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "purchasing/po/". date("Ym/", strtotime($_po_date));
if(ZKP_SQL == 'IDC') {
	if($_po_type == 1) 		$doc_name = substr($_code,0,2)."-". substr($_code,3,2)."-".substr($_code,6,2). "_rev_" . $_revision_time . ".pdf";
	else if($_po_type == 2) $doc_name = substr($_code,0,2)."-". substr($_code,3,3)."-".substr($_code,7,2). "_rev_" . $_revision_time . ".pdf";
} else if(ZKP_SQL == 'MED') {
	$no = explode('/', $_code);

	if ($no[0] < 100) {
		if($_po_type == 1) 		$doc_name = substr($_code,0,2)."-". substr($_code,3,4)."-".substr($_code,8,2). "_rev_" . $_revision_time . ".pdf";
		else if($_po_type == 2)     $doc_name = substr($_code,0,2)."-". substr($_code,3,5)."-".substr($_code,9,2). "_rev_" . $_revision_time . ".pdf";
	} else {
		if($_po_type == 1) 		$doc_name = substr($_code,0,3)."-". substr($_code,4,4)."-".substr($_code,9,2). "_rev_" . $_revision_time . ".pdf";
		else if($_po_type == 2)     $doc_name = substr($_code,0,3)."-". substr($_code,4,5)."-".substr($_code,10,2). "_rev_" . $_revision_time . ".pdf";
	}
}
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>