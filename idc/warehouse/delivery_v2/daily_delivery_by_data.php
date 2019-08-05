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
$s_mode   = isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_cug_code  = isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_dept    = isset($_GET['_dept']) ? $_GET['_dept'] : "all";
$_source  = isset($_GET['cboSource']) ? $_GET['cboSource'] : "all";
$_order_by  = isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "all";

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
$tmp  = array();

if($_order_by == '1') {
  $tmp[0][] = $tmp[1][] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='M'";
} else if($_order_by == '2') {
  $tmp[0][] = $tmp[1][] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='M'";
}

if ($_last_category != 0) {
  $catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
  $tmp[0][] = $tmp[1][] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_source != 'all') {
  $tmp[0][] = "b.out_doc_type = $_source";
  $tmp[1][] = "b.out_doc_type = '".$v_source_doc[$_source] ."'";
}

if ($_cug_code != 'all') {
  //If group specified, 
  $tmp[0][] = $tmp[1][] = "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
  $sql1 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
  $sql2 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
  $sql1 = "
  SELECT
    COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
    'Others') AS cug_name,"; // if null, return Others Group
  $sql2 = "
  SELECT
    COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
    'Others') AS cug_name,"; // if null, return Others Group
}

if($_dept != 'all') {
  if($_dept == 'DEMO') {
    $tmp[0][] = "b.out_doc_type = 6";
    $tmp[1][] = "b.out_doc_type = 'DM'";
  } else {
    $tmp[0][] = "b.out_dept = '$_dept' AND b.out_doc_type != 6";
    $tmp[1][] = "b.out_dept = '$_dept' AND b.out_doc_type != 'DM'";
  }
}

if ($some_date != "") {
  $tmp[0][] = $tmp[1][] = "b.out_cfm_date = DATE '$some_date'";
} else {
  $tmp[0][] = $tmp[1][] = "b.out_cfm_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$tmp[1][] = "b.out_is_revised = false";

$strWhere[0] = implode(" AND ", $tmp[0]);
$strWhere[1] = implode(" AND ", $tmp[1]);

//DEFAULT LIST
$sql1 .= "
 '[' || trim(a.cus_code) || '] ' || a.cus_full_name AS customer_name,
 'v1-'||b.out_idx AS out_idx,
 b.out_code AS deli_code,
 b.out_doc_ref AS doc_ref_code,
 to_char(b.out_cfm_date,'dd-Mon-YY') AS deli_date,
 to_char(b.out_issued_date,'dd-Mon-YY') AS issued_date,
 out_received_by AS issued_by,
 '[' || trim(d.it_code) || '] ' || d.it_model_no AS model_no,
 'v2-'||c.otit_idx AS otit_idx,
 c.otit_qty AS qty,
  CASE
   WHEN out_doc_type = 1 THEN (SELECT bill_remark FROM ".ZKP_SQL."_tb_billing WHERE bill_code = TRIM(b.out_doc_ref))
   WHEN out_doc_type = 2 THEN (SELECT ord_remark FROM ".ZKP_SQL."_tb_order WHERE ord_code = TRIM(b.out_doc_ref))
   WHEN out_doc_type = 3 THEN (SELECT dt_remark FROM ".ZKP_SQL."_tb_dt WHERE dt_code = TRIM(b.out_doc_ref))
   WHEN out_doc_type = 4 THEN (SELECT df_remark FROM ".ZKP_SQL."_tb_df WHERE df_code = TRIM(b.out_doc_ref))
   WHEN out_doc_type = 5 THEN (SELECT dr_remark FROM ".ZKP_SQL."_tb_dr WHERE dr_code = TRIM(b.out_doc_ref))
   WHEN out_doc_type = 6 THEN (SELECT req_remark FROM ".ZKP_SQL."_tb_request WHERE req_code = TRIM(b.out_doc_ref))
  END AS remark,
  e.icat_pidx AS icat_pidx,
  e.icat_midx AS icat_midx,
  it_code as it_code
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_outgoing AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_outgoing_item AS c USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE ". $strWhere[0];

$sql2 .= "
 '[' || trim(a.cus_code) || '] ' || a.cus_full_name AS customer_name,
 'v2-'||b.out_idx AS out_idx,
 b.out_code AS deli_code,
 b.out_doc_ref AS doc_ref_code,
 to_char(b.out_cfm_date,'dd-Mon-YY') AS deli_date,
 to_char(b.out_issued_date,'dd-Mon-YY') AS issued_date,
 out_received_by AS issued_by,
 '[' || trim(d.it_code) || '] ' || d.it_model_no AS model_no,
 'v2-'||c.otit_idx AS otit_idx,
 c.otit_qty AS qty,
  CASE
  WHEN out_doc_type = 'DO Billing' THEN (SELECT bill_remark FROM ".ZKP_SQL."_tb_billing WHERE bill_code = TRIM(b.out_doc_ref))
  WHEN out_doc_type = 'DO Order' THEN (SELECT ord_remark FROM ".ZKP_SQL."_tb_order WHERE ord_code = TRIM(b.out_doc_ref))
  WHEN out_doc_type = 'DT' THEN (SELECT dt_remark FROM ".ZKP_SQL."_tb_dt WHERE dt_code = TRIM(b.out_doc_ref))
  WHEN out_doc_type = 'DF' THEN (SELECT df_remark FROM ".ZKP_SQL."_tb_df WHERE df_code = TRIM(b.out_doc_ref))
  WHEN out_doc_type = 'DR' THEN (SELECT dr_remark FROM ".ZKP_SQL."_tb_dr WHERE dr_code = TRIM(b.out_doc_ref))
  WHEN out_doc_type = 'DM' THEN (SELECT req_remark FROM ".ZKP_SQL."_tb_request WHERE req_code = TRIM(b.out_doc_ref))
 END AS remark,
  e.icat_pidx AS icat_pidx,
  e.icat_midx AS icat_midx,
  it_code as it_code
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_outgoing_v2 AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_outgoing_item_v2 AS c USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE ". $strWhere[1];

$sql = "$sql1 UNION $sql2 ORDER BY icat_pidx, icat_midx, it_code, deli_date, otit_idx";

echo "<pre>";
//echo $sql;
echo "</pre>";
//exit;

print <<<END
  <table>
    <tr>
      <th>MODEL NO</th>
      <th>DOCUMENT NO</th>
      <th>DOCUMENT DATE</th>
      <th>CONFIRM DATE</th>
      <th>ISSUED BY</th>
      <th>SHIP TO CUSTOMER</th>
      <th>QTY</th>
      <th width="300pt">REMARK</th>
    </tr>
END;

$result = & query($sql);
while ($col =& fetchRowAssoc($result)) {
  echo '<tr>';
  echo '<td>'.$col['model_no'].'</td>';
  echo '<td align="center">'.$col['doc_ref_code'].'</td>';
  echo '<td align="center">'.$col['issued_date'].'</td>';
  echo '<td align="center">'.$col['deli_date'].'</td>';
  echo '<td>'.$col['issued_by'].'</td>';
  echo '<td>'.$col['customer_name'].'</td>';
  echo '<td>'.$col['qty'].'</td>';
  echo '<td>'.$col['remark'].'</td>';
  echo '</tr>';
}
echo '</table>';
?>