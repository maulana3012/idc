<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*/
//PREPARE
require LIB_DIR . "fpdf/fpdi.php";

//TEMPORARRY VARIABLE
$_reg_date = date("j-M-Y",strtotime($_reg_date));
//$_rev_no += 1;

//SQL
//Customer List
$sql = "
SELECT *
FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_letter USING (cus_code)
WHERE lt_reg_no = '$_code'
";
$res =& query($sql);
$row = numQueryRows($res);

//Fee
$sql_fee = "SELECT * FROM ".ZKP_SQL."_tb_letter_item WHERE lt_reg_no = '$_code' ";
$fee_res =& query($sql_fee);
$row_fee = numQueryRows($fee_res);

$pdf = new FPDI();

//TEMPLATE PDF ===================================================================================================
$pdf->setSourceFile(APP_DIR . "_include/letter/template_pdf/tpl_receipt_letter.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');
$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);


//============================================================================= CUSTOMER
//Header, Footer
/* 1 */ $pdf->setFont('Arial', 'B', 10);
        $pdf->setXY(153,27);
        $pdf->Cell(45, 4, $_code,0,0,'C');  // Letter No
/* 2 */ $pdf->setFont('Arial', '', 10);
        $pdf->setXY(47,43);
        $pdf->Cell(100, 4, ': ' . $_reg_send_to,0,0,'');    // Letter To
        $pdf->setXY(160,43);
        $pdf->Cell(30, 4, ': ' . $_reg_date,0,0,'');    // Letter To
/* 3 */ $pdf->setXY(47,48);
        $pdf->Cell(100, 4, ': ' . $_cus_name,0,0,'');    // Customer Name
        $pdf->setXY(160,48);
        $pdf->Cell(30, 4, ': ' . $_cus_attn,0,0,'');    // Attn
/* 4 */ $pdf->setXY(29,94);
        $pdf->Multicell(168, 4, $_reg_brief_summary);   // Brief Summary
/* 5 */ $pdf->setXY(42,127);
        $pdf->Cell(38, 4, $_reg_issued_by,0,0,'C');   // Sign By
/* 6 */ $pdf->setFont('Arial', 'I', 7);
        $pdf->setXY(100,107);
        $pdf->Cell(95, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_rev_no),0,0,'R');   // Sign By
        $pdf->setFont('Arial', '', 10);

//Content
$i = 1;
$tot = 0;
$pdf->setY(62);
while($items =& fetchRowAssoc($fee_res, 0)) {
    $pdf->setX(28);
    $pdf->Cell(15, 4, $i++,0,0,'C');  // No
    $pdf->Cell(120, 4, substr($items['lti_desc'],0,35)); // Description
    $pdf->Cell(30, 4, number_format($items['lti_amount'],0),0,1,'R'); // Amount

    $tot += $items['lti_amount'];
}
if($_stamp_pcs > 0) {
    $pdf->setX(28);
    $pdf->Cell(15, 4, $i++,0,0,'C');  // No
    $pdf->Cell(120, 4, "Pemakaian Materai Rp 6.000"); // Description
    $pdf->Cell(30, 4, "$_stamp_pcs pcs",0,1,'R'); // Amount
}
$pdf->setXY(163,86);
$pdf->Cell(30, 4, ($tot > 0) ? number_format($tot,0) : "-",0,0,'R');    // Total




//============================================================================= ADMINISTRATOR
//Header, Footer
/* 1 */ $pdf->setFont('Arial', 'B', 10);
        $pdf->setXY(153,155);
        $pdf->Cell(45, 4, $_code,0,0,'C');  // Letter No
/* 2 */ $pdf->setFont('Arial', '', 10);
        $pdf->setXY(47,171);
        $pdf->Cell(100, 4, ': ' . $_reg_send_to,0,0,'');    // Letter To
        $pdf->setXY(160,171);
        $pdf->Cell(30, 4, ': ' . $_reg_date,0,0,'');    // Letter To
/* 3 */ $pdf->setXY(47,176);
        $pdf->Cell(100, 4, ': ' . $_cus_name,0,0,'');    // Customer Name
        $pdf->setXY(160,176);
        $pdf->Cell(30, 4, ': ' . $_cus_attn,0,0,'');    // Attn
/* 4 */ $pdf->setXY(29,221);
        $pdf->Multicell(168, 4, $_reg_brief_summary);   // Brief Summary
/* 5 */ $pdf->setXY(42,256);
        $pdf->Cell(38, 4, $_reg_issued_by,0,0,'C');   // Sign By
/* 6 */ $pdf->setFont('Arial', 'I', 7);
        $pdf->setXY(100,235);
        $pdf->Cell(95, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_rev_no),0,0,'R');   // Sign By
        $pdf->setFont('Arial', '', 10);

//Content
$i = 1;
$tot = 0;
$pdf->setY(190);
pg_result_seek($fee_res, 0);
while($items =& fetchRowAssoc($fee_res, 0)) {
    $pdf->setX(28);
    $pdf->Cell(15, 4, $i++,0,0,'C');  // No
    $pdf->Cell(120, 4, substr($items['lti_desc'],0,35)); // Description
    $pdf->Cell(30, 4, number_format($items['lti_amount'],0),0,1,'R'); // Amount

    $tot += $items['lti_amount'];
}
if($_stamp_pcs > 0) {
    $pdf->setX(28);
    $pdf->Cell(15, 4, $i++,0,0,'C');  // No
    $pdf->Cell(120, 4, "Pemakaian Materai Rp 6.000"); // Description
    $pdf->Cell(30, 4, "$_stamp_pcs pcs",0,1,'R'); // Amount
}
$pdf->setXY(163,214.5);
$pdf->Cell(30, 4, ($tot > 0) ? number_format($tot,0) : "-",0,0,'R');    // Total



//PRINT END ======================================================================================================
//detimine the document name & document path.
$_code_no   = substr($_GET['_code'],0,4);
$_code_type = substr($_GET['_code'],9,1);
$storage = PDF_STORAGE . "letter/$moduleDept/". date("Ym/", strtotime($_reg_date));
$doc_name = $_code_no.$_code_type . "_rev_" . $_rev_no .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>