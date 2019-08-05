<?php

$directory	= explode("/",$_SERVER['PHP_SELF']);
$base_url	= $directory[1];
$currentDept	= $directory[2];
$moduleDept	= $directory[3];
if($currentDept == 'customer_service') $department  = 'S';
else $department  = strtoupper(substr($currentDept,0,1));

$cus_channel = array('A'=>'002', 'D'=>'000', 'H'=>'005', 'M'=>'007', 'P'=>'004', 'T'=>'007', 'S'=>'00S');

$ordby	= array('ALL'=>array(0=>array('all','==ALL=='), 1=>array('1','INDOCORE'), 2=>array('2','MEDIKUS EKA'), 3=>array('3','MEDISINDO'), 4=>array('4','SAMUDIA'), 5=>array('5','IDC &amp; MEP'), 6=>array('6','MED &anmp; SMD')), 
		'IDC'=>array(0=>array('1','INDOCORE'), 0=>array('1','MEDIKUS EKA')), 
		'MED'=>array(0=>array('2','MEDISINDO')),
		'MED'=>array(0=>array('3','SAMUDIA'))
	);

$cboFilter   = array(
	0=>array(	// title
	  'ALL'=>array(0=>array('- PT MEDISINDO BAHANA MANAGEMENT SYSTEM -','PT MEDISINDO BAHANA MANAGEMENT SYSTEM')),
	  'IDC'=>array(0=>array('- PT INDOCORE PERKASA MANAGEMENT SYSTEM -','PT INDOCORE PERKASA MANAGEMENT SYSTEM')), 
	  'MEP'=>array(0=>array('- PT MEDIKUS EKA PERKASA MANAGEMENT SYSTEM -','PT MEDIKUS EKA PERKASA MANAGEMENT SYSTEM')), 
	  'MED'=>array(0=>array('- PT MEDISINDO BAHANA MANAGEMENT SYSTEM -','PT MEDISINDO BAHANA MANAGEMENT SYSTEM')),
	  'SMD'=>array(0=>array('- PT SAMUDIA BAHTERA MANAGEMENT SYSTEM -','PT SAMUDIA BAHTERA MANAGEMENT SYSTEM'))),
	1=>array(	// cboOrderBy
	  'ALL'=>array(0=>array('all','==ALL=='), 1=>array('1','INDOCORE'), 2=>array('2','MEDIKUS'), 3=>array('3','MEDISINDO'), 4=>array('4','SAMUDIA'), 5=>array('5','IDC &amp; MEP'), 6=>array('6','MED &amp; SMD')), 
	  'IDC'=>array(0=>array('1','INDOCORE')), 
	  'MEP'=>array(0=>array('2','MEDIKUS EKA')), 
	  'MED'=>array(0=>array('1','MEDISINDO')),
	  'SMD'=>array(0=>array('2','SAMUDIA'))),
	2=>array(	// cboFilterVat
	  'ALL'=>array(0=>array('all','==ALL=='), 1=>array('vat','VAT'), 2=>array('vat-IO','VAT - IO'), 3=>array('vat-IP','VAT - IP'), 4=>array('non','NON VAT')), 
	  'IDC'=>array(0=>array('all','==ALL=='), 1=>array('vat','VAT'), 2=>array('vat-IO','VAT - IO'), 3=>array('vat-IP','VAT - IP'), 4=>array('non','NON VAT')),
	  'MEP'=>array(0=>array('all','==ALL=='), 1=>array('non','NON VAT')),
	  'MED'=>array(0=>array('all','==ALL=='), 1=>array('vat','VAT'), 2=>array('vat-IO','VAT - IO'), 3=>array('vat-IP','VAT - IP'), 4=>array('non','NON VAT')),
  	  'SMD'=>array(0=>array('all','==ALL=='), 1=>array('vat','VAT'), 2=>array('vat-IO','VAT - IO'), 3=>array('vat-IP','VAT - IP'), 4=>array('non','NON VAT'))),
	3=>array(	// cboFilterWarehouseStock
	  'purchasing'=>array(
		'ALL'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'IDC'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'MED'=>array(0=>array('1','MEDISINDO')),
		'SMD'=>array(0=>array('1','MEDISINDO'))),
	  'warehouse'=>array(
		'ALL'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'IDC'=>array(0=>array('1','IDC'), 1=>array('2','DNR')),
		'MED'=>array(0=>array('1','MEDISINDO')),
		'SMD'=>array(0=>array('1','MEDISINDO'))),
	  'do'=>array(
		'ALL'=>array(0=>array('1','INDOCORE'), 1=>array('2','MEDIKUS'), 2=>array('1','MEDISINDO'), 3=>array('2','SAMUDIA')),
		'IDC'=>array(0=>array('1','INDOCORE'), 1=>array('2','MEDIKUS')),
		'MED'=>array(0=>array('1','MEDISINDO'), 1=>array('2','SAMUDIA')),
	))
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
} else if(ZKP_URL == 'SMD') {
	$company[] = "PT. Samudia Bahtera";
	$company[] = "Rukan Graha Cempaka Mas Blok E 15 Jl. Letjen Suprapto No.1 - Jakarta Pusat";
	$company[2][] = "Tel: (021) 4288 1130 (hunting)   Fax: (021) 4288 1135";
}

$tabFilter	= array(0=>array("ALL"=>1, "IDC"=>2, "MED"=>4, "MEP"=>8, "SMD"=>16),
					1=>array(
						 "admin"=>31,
						 "apotik"=>30,
						 "dealer"=>30,
						 "hospital"=>30,
						 "marketing"=>30,
						 "pharmaceutical"=>30,
						 "tender"=>30,
						 "customer_service"=>30,
						 "accounting"=>33,
						 "report"=>30,
						 "report_all"=>31,
						 "incentive"=>31,
						 "purchasing"=>22,
						 "warehouse"=>22,
						 "demo"=>22,
						 "event"=>22,
						 "product"=>22
					));
?>