<?php
//SET WHERE PARAMETER
$tmp = array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_type != 'all') {
	$tmp[]   = "e_type = $_type"; 
}

#periode

$strWhere = implode(" AND ", $tmp);


$sql = "
";
?>