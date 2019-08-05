<?php
// index
// 0  => it_code
// 1  => icat_midx
// 2  => it_model_no
// 3  => it_type
// 4  => it_desc
// 5  => real_stock
// 6  => est_stock
//REQUIRE
require_once "../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//Check PARAMETER
$_dept		= $_GET['_dept'];
$_status	= isset($_GET['_status']) ? $_GET['_status'] : 0;
$_location	= (isset($_GET['_location'])) ? trim($_GET['_location']):1;
$_lastCategoryNo = 0;
$strGet		= "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT 
  it_code,
  icat_midx,
  it_model_no,
  it_type,
  it_desc,
  ".ZKP_SQL."_getReadyStock(it_code,'$_dept') AS real_stock,
  ".ZKP_SQL."_getReadyStock(it_code,'$_dept') - ".ZKP_SQL."_getBookedStock(NULL,it_code) AS est_stock,
  ".ZKP_SQL."_getBookedStock(NULL,it_code) AS book_qty
FROM ".ZKP_SQL."_tb_item
");

$sqlQuery->whereCaluse = "it_status = 0";

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];
	
	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ") AND it_status = 0";

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_lastCategoryNo))) {
		$M->goErrorPage($path, "javascript:window.close();");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
	$strGet = "&lastCategoryNo=" . $_lastCategoryNo;
}

//Search Option 2 : by model no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("it_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=code_no";
}

$findStrGet = str_replace('curpage=','',getQueryString());
$strGet		= $findStrGet . "&_status=$_status";

$sqlQuery->setOrderBy("it_code");
$result =& query($sqlQuery->getSQL());

//echo "<pre>";
//echo $sqlQuery->getSQL();
echo "var it = new Array();\n";
$i = 0;
while ($rows =& fetchRow($result,0)) {
	$rows[] = $i;
	foreach($rows as $key => $val) {
		$rows[$key] = trim(addslashes($val));
	}
	$element = implode("', '", $rows);
	echo "it[".$i."] = ['".$element."'];\n";
	$i++;
}
?>