<?php
// REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

///Make sure that a value was sent.
if (isset($_GET['it_code']) && $_GET['it_code'] != '' &&
	isset($_GET['dept']) && $_GET['dept'] != ''
	) {

	//Add slashes to any quotes to avoid SQL problems.
	$it_code	= addslashes($_GET['it_code']);
	$dept		= $_GET['dept'];
	//Get every page title for the site.
	$sql	= "
	SELECT 
		it_code,
		icat_midx,
		it_model_no,
		it_type,
		it_desc,
		".ZKP_SQL."_getReadyStock(it_code,'$dept') AS real_stock,
		".ZKP_SQL."_getReadyStock(it_code,'$dept') - ".ZKP_SQL."_getBookedStock(NULL,it_code) AS est_stock
	FROM ".ZKP_SQL."_tb_item
	WHERE it_code = '$it_code'
	";

	$result	=& query($sql);
	$i = 0;
	while ($rows =& fetchRow($result,0)) {
		$rows[] = $i;
		foreach($rows as $key => $val) {
			echo trim(addslashes($val)) . "\n";
			//$rows[$key] = trim(addslashes($val));
		}/*
		$element = implode("', '", $rows);
		echo "it[".$i."] = ['".$element."'];\n";
		$i++;*/
	}
}
?>