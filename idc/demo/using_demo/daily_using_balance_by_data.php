<?php header('Expires: 0');
header('Cache-control: private');
header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename="daily_using_balance_by_data.xls"'); 

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//Global
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
  $_last_category = 0;
}

$_dept    = isset($_GET['cboDept']) ? $_GET['cboDept'] : "all";
$s_mode   = isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_cus_code  = isset($_GET['_cus_code']) ? $_GET['_cus_code'] : "";

if($s_mode == 'period') {
  $some_date    = "";
  $period_from  = isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-604800);
  $period_to    = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {

  if(isset($_GET['some_date'])) {
    $some_date = $_GET['some_date'];
  } else {
    $some_date = date('j-M-Y');
    $_GET['cboDate'] = "0";
  }

  $period_from    = "";
  $period_to      = "";
}

//SET WHERE PARAMETER
$tmp = array();

if ($_last_category != 0) {
  $catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
  $tmp[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cus_code != "") {
  $tmp[]  = "use_cus_to = '$_cus_code'";
}

if ($some_date != "") {
  $tmp[]  = "use_request_date = DATE '$some_date'";
} else {
  $tmp[]  = "use_request_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_dept != 'all') {
  $tmp[]  = "use_dept = '$_dept'";
}

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql = "
SELECT
  icat_midx,
  icat_pidx,
  it_code,
  it_model_no,
  '[' || trim(it_code) || ']' || it_model_no AS model_no,
  use_code,
  to_char(use_request_date,'dd-Mon-YY') AS request_date,
  to_char(use_request_date,'YYYYMM') AS period,
  use_request_by,
  use_cus_to,
  use_cus_name,
  '[' || trim(use_cus_to) || ']' || use_cus_name AS customer_name,
  usit_qty AS request_qty,
  (SELECT sum(rdit_qty) FROM ".ZKP_SQL."_tb_using_demo JOIN ".ZKP_SQL."_tb_return_demo USING(use_code) JOIN ".ZKP_SQL."_tb_return_demo_item USING(red_code) WHERE use_code=b.use_code AND it_code=c.it_code) AS return_qty,
  to_char(".ZKP_SQL."_getLastReturnDate(use_code, it_code), 'dd-Mon-YY') AS last_return_date,
  'confirm_request.php?_code=' || use_code AS go_page,
  use_request_date,
  use_remark
FROM
 ".ZKP_SQL."_tb_using_demo AS a
 JOIN ".ZKP_SQL."_tb_using_demo_item AS b USING(use_code)
 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx) 
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, use_request_date, use_code";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
exit;
*/
print <<<END
  <table>
    <th width="15%">MODEL NO</th>
      <th width="13%">REQUEST NO.</th>
      <th width="8%">REQUEST DATE</th>
      <th width="3%">PERIOD</th>
      <th width="8%">REQUEST BY</th>
      <th>CUSTOMER / EVENT</th>
      <th width="7%">USE<br />QTY</th>
      <th width="7%">RTRN<br />QTY</th>
      <th width="7%">BAL<br />QTY</th>
      <th>DOCUMENT REMARK</th>
    </tr>
END;

$result = & query($sql);
while ($col =& fetchRowAssoc($result)) {
  $bal  = $col['request_qty'] - $col['return_qty'];
  echo '<tr>';
  echo '<td>'.$col['model_no'].'</td>';
  echo '<td align="center">'.$col['use_code'].'</td>';
  echo '<td align="center">'.$col['use_request_date'].'</td>';
  echo '<td align="center">'.$col['period'].'</td>';
  echo '<td>'.$col['use_request_by'].'</td>';
  echo '<td>'.$col['customer_name'].'</td>';
  echo '<td>'.$col['request_qty'].'</td>';
  echo '<td>'.$col['return_qty'].'</td>';
  echo '<td>'.$bal.'</td>';
  echo '<td>'.$col['use_remark'].'</td>';
  echo '</tr>';
}
echo '</table>';
?>
