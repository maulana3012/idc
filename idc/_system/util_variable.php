<?php

$directory 		= explode("/",$_SERVER['PHP_SELF']);
$base_url		= $directory[1];
$currentDept	= $directory[2];
$moduleDept		= $directory[3];
if($currentDept == 'customer_service') $department  = 'C';
else $department  = strtoupper(substr($currentDept,0,1));

$cus_channel = array('A'=>'002', 'D'=>'000', 'H'=>'005', 'M'=>'007', 'P'=>'004', 'T'=>'004', 'S'=>'00S');

$ordby		= array('ALL'=>array(0=>array('all','==ALL=='), 1=>array('1','INDOCORE'), 2=>array('2','MEDIKUS EKA'), 3=>array('3','MEDISINDO')),
					'IDC'=>array(0=>array('1','INDOCORE'), 0=>array('1','MEDIKUS EKA')),
					'MED'=>array(0=>array('2','MEDISINDO'))
			  );

$cboFilter   = array(
	0=>array(	// title
	  'ALL'=>array(0=>array('- PT INDOCORE PERKASA MANAGEMENT SYSTEM -','PT INDOCORE PERKASA MANAGEMENT SYSTEM')),
	  'IDC'=>array(0=>array('- PT INDOCORE PERKASA MANAGEMENT SYSTEM -','PT INDOCORE PERKASA MANAGEMENT SYSTEM')),
	  'MEP'=>array(0=>array('- PT MEDIKUS EKA PERKASA MANAGEMENT SYSTEM -','PT MEDIKUS EKA PERKASA MANAGEMENT SYSTEM')),
	  'MED'=>array(0=>array('- PT MEDISINDO BAHANA MANAGEMENT SYSTEM -','PT MEDISINDO BAHANA MANAGEMENT SYSTEM'))),
	1=>array(	// cboOrderBy
	  'ALL'=>array(0=>array('all','==ALL=='), 1=>array('1','INDOCORE'), 2=>array('2','MEDISINDO'), 3=>array('3','MEDIKUS EKA')),
	  'IDC'=>array(0=>array('1','INDOCORE')),
	  'MEP'=>array(0=>array('2','MEDIKUS EKA')),
	  'MED'=>array(0=>array('1','MEDISINDO'))),
	2=>array(	// cboFilterVat
	  'ALL'=>array(0=>array('all','==ALL=='), 1=>array('vat','VAT'), 2=>array('vat-IO','VAT - IO'), 3=>array('vat-IP','VAT - IP'), 4=>array('non','NON VAT')),
	  'IDC'=>array(0=>array('all','==ALL=='), 1=>array('vat','VAT'), 2=>array('vat-IO','VAT - IO'), 3=>array('vat-IP','VAT - IP'), 4=>array('non','NON VAT')),
	  'MEP'=>array(0=>array('all','==ALL=='), 1=>array('non','NON VAT')),
	  'MED'=>array(0=>array('all','==ALL=='), 1=>array('vat','VAT'), 2=>array('vat-IO','VAT - IO'), 3=>array('vat-IP','VAT - IP'), 4=>array('non','NON VAT'))),
	3=>array(	// cboFilterWarehouseStock
	  'purchasing'=>array(
		'ALL'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'IDC'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'MED'=>array(0=>array('1','MEDISINDO'))),
	  'warehouse'=>array(
		'ALL'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'IDC'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'MED'=>array(0=>array('1','MEDISINDO'))))
);

$v_log_in	= "'Initial Stock', 'PL Import', 'PL Claim', 'PL Local', 'Return Billing', 'Return Order', 'Return DT', 'Move Type (PO)', 'Move Type (Return No Only)'";
$v_log_out	= "'DO Order', 'DO Billing', 'DF', 'DT', 'DR', 'DM', 'Reject Stock', 'Reject ED', 'Move Type (Billing No Only)'";
$v_source_doc	= array(1=>'DO Billing', 2=>'DO Order', 3=>'DT', 4=>'DF', 5=>'DR', 6=>'DM');

$company = array();
if(ZKP_URL == 'IDC') {
	$company[] = "PT. Indocore Perkasa";
	$company[] = "Graha Mas Pemuda Blok AB 19 Jl. Pemuda - Jakarta Timur 13220";
	$company[2][] = "Tel: (021) 4788 2599 (hunting)   Fax: (021) 4788 2598";
	$company[2][] = "e-mail: indocore@indocore.co.id   Web-site: http://www.indocore.co.id";
} else if(ZKP_URL == 'MEP') {
	$company[] = "PT. Medikus Eka Perkasa";
	$company[] = "Graha Mas Pemuda Blok AB 19 Jl. Pemuda - Jakarta Timur 13220";
	$company[2][] = "Tel: (021) 4788 2599 (hunting)   Fax: (021) 4788 2598";
} else if(ZKP_URL == 'MED') {
	$company[] = "PT. Medisindo Bahana";
	$company[] = "Rukan Graha Cempaka Mas Blok E 15 Jl. Letjen Suprapto No.1 - Jakarta Pusat";
	$company[2][] = "Tel: (021) 425 0665 (hunting)   Fax: (021) 425 0703";
}

$auth = array(
		"input_efaktur" => array("esti", "devi", "neki", "Maulana"),
	);


$tabFilter	= array(0=>array("ALL"=>1, "IDC"=>2, "MED"=>4, "MEP"=>8),
					1=>array(
						 "admin"=>15,
						 "apotik"=>14,
						 "dealer"=>14,
						 "hospital"=>14,
						 "marketing"=>14,
						 "pharmaceutical"=>14,
						 "tender"=>14,
						 "customer_service"=>14,
						 "accounting"=>6,
						 "report"=>14,
						 "report_all"=>15,
						 "incentive"=>15,
						 "purchasing"=>6,
						 "warehouse"=>6,
						 "demo"=>6,
						 "event"=>6,
						 "product"=>6
					));
?>
