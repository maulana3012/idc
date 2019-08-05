<?php header('Expires: 0');
header('Cache-control: private');
header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename="data_outgoing_summary_by_item.xls"'); 

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//Global
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

$tmp[]  = "use_cfm_marketing_timestamp IS NOT NULL";

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql = "
SELECT
  icat_midx,
  icat_pidx,
  it_code,
  it_model_no,
  '[' || trim(it_code) || '] ' || it_model_no AS model_no,
  use_code,
  to_char(use_request_date,'dd-Mon-yy') AS use_request_date,
  to_char(use_request_date,'YYYYMM') AS period,
  use_request_by,  
  use_cus_to,
  use_cus_name,
  '[' || trim(use_cus_to) || '] ' || use_cus_name AS customer_name,  
  usit_qty AS qty,
  use_remark AS remark
FROM
 ".ZKP_SQL."_tb_using_demo AS a
 JOIN ".ZKP_SQL."_tb_using_demo_item AS b USING(use_code)
 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS d USING(icat_midx) 
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, use_request_date";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
exit;
*/
print <<<END
  <table>
    <tr>
      <th>MODEL NO</th>
      <th>DOCUMENT NO</th>
      <th>DOCUMENT DATE</th>
      <th>PERIOD</th>
      <th>REQUEST BY</th>
      <th>SHIP TO CUSTOMER</th>
      <th>QTY</th>
      <th>REMARK</th>
    </tr>
END;

$result = & query($sql);
while ($col =& fetchRowAssoc($result)) {
  echo '<tr>';
  echo '<td>'.$col['model_no'].'</td>';
  echo '<td align="center">'.$col['use_code'].'</td>';
  echo '<td align="center">'.$col['use_request_date'].'</td>';
  echo '<td align="center">'.$col['period'].'</td>';
  echo '<td>'.$col['use_request_by'].'</td>';
  echo '<td>'.$col['customer_name'].'</td>';
  echo '<td>'.$col['qty'].'</td>';
  echo '<td>'.$col['remark'].'</td>';
  echo '</tr>';
}
echo '</table>';
?>