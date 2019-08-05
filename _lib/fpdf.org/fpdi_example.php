<?php
require('fpdi.php');

$pdf = new FPDI();

$pagecount = $pdf->setSourceFile('TestDoc.pdf');
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx);

$pdf->Output('newpdf.pdf', 'D');
?>