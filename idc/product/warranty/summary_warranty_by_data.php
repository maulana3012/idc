<?php header('Expires: 0');
header('Cache-control: private');
header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename="data_warranty.xls"'); 

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//Global
$left_loc		= "summary_warranty_by_item.php";
$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";
$_date 			= isset($_GET['cboDateBy'])? $_GET['cboDateBy'] : "purchase";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time() - 2592000);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {

	if(isset($_GET['some_date'])) {
		$some_date = $_GET['some_date'];
	} else {
		$some_date = date('j-M-Y');
		$_GET['cboDate'] = "0";
	}

	$period_from 		= "";
	$period_to 			= "";
}

if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_last_category = $_GET['lastCategoryNo'];

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_last_category))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
} else {
	$_last_category	= 0;
}


//SET WHERE PARAMETER
$tmp = array();

if($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"wr_cus_city", "byAddress"=>"wr_cus_address", "byModelNo"=>"it_model_no", "byWarrantyNo"=>"wr_warranty_no", "byStore"=>"wr_purchase_store");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
}

if($_date == 'purchase')
{
	if ($some_date != "") {
		$tmp[] = "wr_purchase_date = DATE '$some_date'";
	} else {
		$tmp[] = "wr_purchase_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	}
} else if($_date == 'input')
{
	if ($some_date != "") {
		$tmp[] = "wr_inputted_timestamp BETWEEN '$some_date 00:00' AND '$some_date 23:59'";
	} else {
		$tmp[] = "wr_inputted_timestamp BETWEEN '$period_from 00:00' AND '$period_to 23:59'";
	}
}

$strWhere = implode(" AND ", $tmp);


$sql = "
SELECT 
	icat_pidx,
	icat_midx,
	wr_idx,
	a.it_code,
	it_model_no,
	'[' || trim(a.it_code) || '] ' || trim(it_model_no) as model,	
	wr_warranty_no,
	wr_serial_no,
	to_char(wr_purchase_date, 'yyyy') as wr_purchase_year,
	to_char(wr_purchase_date, 'yyyymm') as wr_purchase_period,
	to_char(wr_purchase_date, 'yyyy-mm-dd') as wr_purchase_date,
	wr_cus_name,
	wr_cus_sex,
	wr_cus_phone,
	wr_cus_hphone,
	wr_cus_email,	
	wr_cus_address,
	wr_purchase_store,
	wr_cus_city,	
	to_char(wr_inputted_timestamp, 'yyyy-mm-dd') as wr_inputted_timestamp	
FROM 
	".ZKP_SQL."_tb_warranty AS a 
	JOIN ".ZKP_SQL."_tb_item AS b ON(a.it_code = b.it_code) JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE ". $strWhere ."
ORDER BY a.it_code, wr_purchase_date
";

echo '<table>';
echo '<tr>';
if($some_date != "") {
	echo "<td colspan=\"10\">Search by $_date date $some_date</td>";
} else {
	echo "<td colspan=\"10\">Search by $_date date $period_from AND $period_to</td>";
}
echo '</tr>';


print <<<END
<tr>
  <th>MODEL</th>
  <th>WARRANTY NO</th>
  <th>SERIAL NO</th>
  <th>PURCHASE DATE</th>
  <th>YEAR</th>
  <th>PERIOD</th>
  <th>CUSTOMER NAME</th>
  <th>SEX</th>
  <th>PHONE</th>      
  <th>HANDPHONE</th>
  <th>EMAIL</th>
  <th>ADDRESS</th>
  <th>STORE</th>
  <th>CITY</th>
  <th>INPUT TIME</th>
</tr>
END;


$result = & query($sql);
while ($col =& fetchRowAssoc($result)) {
  echo '<tr>';
  echo '<td>'.$col['model'].'</td>';
  echo '<td>\''.$col['wr_warranty_no'].'</td>';
  echo ($col['wr_serial_no'] != "") ?  '<td>\''.$col['wr_serial_no'].'</td>' : '<td></td>';
  echo '<td>'.$col['wr_purchase_date'].'</td>';
  echo '<td>'.$col['wr_purchase_year'].'</td>';
  echo '<td>'.$col['wr_purchase_period'].'</td>';
  echo '<td>'.$col['wr_cus_name'].'</td>';
  echo '<td>'.$col['wr_cus_sex'].'</td>';
  echo '<td>'.$col['wr_cus_phone'].'</td>';
  echo ($col['wr_cus_hphone'] != "") ?  '<td>\''.$col['wr_cus_hphone'].'</td>' : '<td></td>';
  echo '<td>'.$col['wr_cus_email'].'</td>';
  echo '<td>'.$col['wr_cus_address'].'</td>';
  echo '<td>'.$col['wr_purchase_store'].'</td>';
  echo '<td>'.$col['wr_cus_city'].'</td>';
  echo '<td>'.$col['wr_inputted_timestamp'].'</td>';
  echo '</tr>';
}
echo '</table>';
?>

