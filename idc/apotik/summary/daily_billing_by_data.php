<?php header('Expires: 0');
header('Cache-control: private');
header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename="data_billing.xls"');

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//Global
$s_mode     = isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_cug_code    = isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_marketing   = isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_order_by    = isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_filter_doc  = isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_vat     = isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
$_paper     = isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$_paper     = isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$cboSearchType  = isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch    = isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

if($s_mode == 'period') {
  $some_date    = "";
  $period_from  = isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time() - 2592000);
  $period_to    = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {

  if(isset($_GET['some_date'])) {
    $some_date = $_GET['some_date'];
  } else {
    $some_date = date('j-M-Y');
  }
  $period_from    = "";
  $period_to      = "";
}

//SET WHERE PARAMETER
$tmp_bill = array();
$tmp_turn = array();
$tmp_dr   = array();
if($department=='A') $t_col = 5;
else $t_col = 4;

if ($_last_category != 0) {
  $catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
  $tmp_bill[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
  $tmp_turn[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
  $tmp_dr[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if(ZKP_FUNCTION == 'ALL') {
  if($_order_by != 'all'){
    $tmp_bill[] = "bill_ordered_by = $_order_by";
    $tmp_turn[] = "turn_ordered_by = $_order_by";
    $tmp_dr[] = "dr_ordered_by = $_order_by";
  }
} else {
  $tmp_bill[] = "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
  $tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
  $tmp_dr[] = "dr_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
  $tmp_bill[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
  $tmp_turn[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', turn_code,'billing_return')";
  $tmp_dr[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', dr_code,'dr')";
}

if($_marketing != "all") {
  $tmp_bill[] = "cus_responsibility_to = $_marketing";
  $tmp_turn[] = "cus_responsibility_to = $_marketing";
  $tmp_dr[] = "cus_responsibility_to = $_marketing";
}

if($_filter_doc == 'I'){
  $tmp_turn[] = "turn_code = ''";
  $tmp_dr[] = "dr_code = ''";
} else if($_filter_doc == 'R') {
  $tmp_bill[] = "bill_code = ''";
  $tmp_dr[] = "dr_code = ''";
} else if($_filter_doc == 'DR') {
  $tmp_bill[] = "bill_code = ''";
  $tmp_turn[] = "turn_code = ''";
}

if ($_cug_code != 'all') {
  $tmp_bill[] = "bill_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
  $tmp_turn[] = "turn_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
  $tmp_dr[] = "dr_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
  $sql_bill   = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
  $sql_return = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
  $sql_dr   = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
  $sql_bill = "
  SELECT
    COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
    'Others') AS cug_name,";
  $sql_return = "
  SELECT
    COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = turn_ship_to),
    'Others') AS cug_name,";
  $sql_dr = "
  SELECT
    COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = dr_ship_to),
    'Others') AS cug_name,";
}

if ($some_date != "") {
  $tmp_bill[] = "bill_inv_date = DATE '$some_date'";
  $tmp_turn[] = "turn_return_date = DATE '$some_date'";
  $tmp_dr[] = "dr_issued_date = DATE '$some_date'";
} else {
  $tmp_bill[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
  $tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
  $tmp_dr[] = "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_vat == 'vat') {
  $tmp_bill[] = "bill_vat > 0";
  $tmp_turn[] = "turn_vat > 0";
  $tmp_dr[] = "dr_type_item = 1";
} else if($_vat == 'vat-IO') {
  $tmp_bill[] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
  $tmp_turn[] = "turn_vat > 0";
  $tmp_dr[] = "dr_type_item = 1";
} else if($_vat == 'vat-IP') {
  $tmp_bill[] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
  $tmp_turn[] = "turn_code is null";
  $tmp_dr[] = "dr_code is null";
} else if ($_vat == 'non') {
  $tmp_bill[] = "bill_vat = 0";
  $tmp_turn[] = "turn_vat = 0";
  $tmp_dr[] = "dr_type_item = 2";
}

if ($_paper == '0') {
  $tmp_bill[] = "bill_type_invoice = '0'";
  $tmp_turn[] = "turn_paper = 0";
} else if ($_paper == '1') {
  $tmp_bill[] = "bill_type_invoice = '1'";
  $tmp_turn[] = "turn_paper = 1";
  $tmp_dr[] = "dr_code is null";
} else if ($_paper == 'A') {
  $tmp_bill[] = "bill_paper_format = 'A'";
  $tmp_turn[] = "turn_paper = 0";
  $tmp_dr[] = "dr_code is null";
} else if ($_paper == 'B') {
  $tmp_bill[] = "bill_paper_format = 'B'";
  $tmp_turn[] = "turn_paper = 1";
  $tmp_dr[] = "dr_code is null";
}

if($cboSearchType != '' && $txtSearch != '') {
  $type = array("byCity"=>"cus_city");
  $tmp_bill[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
  $tmp_turn[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
  $tmp_dr[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
  $get[] = "$cboSearchType=$txtSearch";
}

$tmp_bill[] = "bill_dept = '$department'";
$tmp_turn[] = "turn_dept = '$department'";
$tmp_dr[] = "dr_dept = '$department'";

$strWhereBilling = implode(" AND ", $tmp_bill);
$strWhereReturn  = implode(" AND ", $tmp_turn);
$strWhereDR    = implode(" AND ", $tmp_dr);

//DEFAULT LIST
$sql_bill .= "
  'bill-'||biit_idx as idx,
  TRIM(b.bill_ship_to) AS ship_to,
  b.bill_ship_to_name AS ship_to_name,
  b.bill_code AS invoice_code,
  to_char(b.bill_inv_date, 'dd-Mon-YY') AS invoice_issue_date,
  to_char(b.bill_inv_date, 'Mon') AS invoice_month,
  to_char(b.bill_inv_date, 'YYYY') AS invoice_year,
  to_char(b.bill_inv_date, 'YYMM') AS invoice_period,
  ".ZKP_SQL."_getcategorypathitem(it.it_code,1) AS category,
  ".ZKP_SQL."_getcategorypathitem2(it.it_code,1) AS category2,
  $$'$$||TRIM(it.it_code) AS it_code,
  it.it_model_no AS it_model_no,
  ROUND(bi.biit_unit_price * (1 - b.bill_discount/100),0)::float AS unit_price,
  bi.biit_qty::float AS qty,
  ROUND((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float AS amount,
  ROUND((b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float AS vat,
  ROUND((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float +
  ROUND((b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float AS amount_vat,
  b.bill_inv_date AS invoice_date,
  a.ma_displayname AS me,
  c.cus_city as area
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_billing_item AS bi USING(bill_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN tb_mbracc AS a ON ma_idx = b.bill_responsible_by
WHERE $strWhereBilling";

$sql_return .= "
  'turn-'||reit_idx as idx,
  TRIM(t.turn_ship_to) AS ship_to,
  t.turn_ship_to_name AS ship_to_name,
  t.turn_code AS invoice_code,
  to_char(t.turn_return_date, 'dd-Mon-YY') AS invoice_issued_date,
  to_char(t.turn_return_date, 'Mon') AS invoice_month,
  to_char(t.turn_return_date, 'YYYY') AS invoice_year,
  to_char(t.turn_return_date, 'YYMM') AS invoice_period,
  ".ZKP_SQL."_getcategorypathitem(it.it_code,1) AS category,
  ".ZKP_SQL."_getcategorypathitem2(it.it_code,1) AS category2,
  $$'$$||TRIM(it.it_code) AS it_code,
  it.it_model_no AS it_model_no,
  ROUND(ti.reit_unit_price * (1 - t.turn_discount/100),0)::float AS unit_price,
  (ti.reit_qty*-1)::float AS qty,
  (ROUND((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float AS amount,
  (ROUND((t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float AS vat,
  (ROUND((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float +
  (ROUND((t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float AS amount_vat,
  t.turn_return_date AS invoice_date,
  a.ma_displayname AS me,
  c.cus_city as area
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_return AS t ON t.turn_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_return_item AS ti USING(turn_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN tb_mbracc AS a ON ma_idx = t.turn_responsible_by
WHERE $strWhereReturn" ;

$sql_dr .= "
  'dr-'||drit_idx as idx,
  TRIM(b.dr_ship_to) AS ship_to,
  b.dr_ship_name AS ship_to_name,
  b.dr_code AS invoice_code,
  to_char(b.dr_issued_date, 'dd-Mon-YY') AS invoice_issue_date,
  to_char(b.dr_issued_date, 'Mon') AS invoice_month,
  to_char(b.dr_issued_date, 'YYYY') AS invoice_year,
  to_char(b.dr_issued_date, 'YYMM') AS invoice_period,
  ".ZKP_SQL."_getcategorypathitem(it.it_code,1) AS category,
  ".ZKP_SQL."_getcategorypathitem2(it.it_code,1) AS category2,
  $$'$$||TRIM(it.it_code) AS it_code,
  it.it_model_no AS it_model_no,
  null AS unit_price,
  (bi.drit_qty)::float AS qty,
  null AS amount,
  null AS vat,
  null AS amount_vat,
  b.dr_issued_date AS invoice_date,
  a.ma_displayname AS me,
  c.cus_city as area
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_dr AS b ON dr_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_dr_item AS bi USING(dr_code)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN tb_mbracc AS a ON ma_account = b.dr_received_by
WHERE " . $strWhereDR ;

$sql = "$sql_bill UNION $sql_return UNION $sql_dr ORDER BY cug_name, ship_to, invoice_date, invoice_code, it_code, me, area";

#echo "<pre>";
//echo $sql;
#echo "</pre>";
///exit;

print <<<END
  <table>
    <tr>
      <th>GROUP</th>
      <th>CUS CODE</th>
      <th>SHIP TO CUSTOMER</th>
      <th>AREA</th>
      <th>ME</th>
      <th>DOCUMENT NO</th>
      <th>DOCUMENT DATE</th>
      <th>PERIOD</th>
      <th>YEAR</th>
      <th>MONTH</th>
      <th>CATEGORY</th>
      <th>CATEGORY2</th>
      <th>ITEM CODE</th>
      <th>MODEL NO</th>
      <th>PRICE</th>
      <th>QTY</th>
      <th>AMOUNT</th>
      <th>VAT</th>
      <th>TOTAL</th>
    </tr>
END;

$result = & query($sql);
while ($col =& fetchRowAssoc($result)) {
  $cat = explode(", ", $col['category']);
  echo '<tr>';
  echo '<td>'.$col['cug_name'].'</td>';
  echo '<td>'.$col['ship_to'].'</td>';
  echo '<td>'.$col['ship_to_name'].'</td>';
  echo '<td>'.$col['area'].'</td>';
  echo '<td>'.$col['me'].'</td>';
  echo '<td>'.$col['invoice_code'].'</td>';
  echo '<td>'.$col['invoice_issue_date'].'</td>';
  echo '<td>'.$col['invoice_period'].'</td>';
  echo '<td>'.$col['invoice_year'].'</td>';
  echo '<td>'.$col['invoice_month'].'</td>';
  echo '<td>'.$cat[0].'</td>';
  echo '<td>'.$col['category2'].'</td>';
  echo '<td>'.$col['it_code'].'</td>';
  echo '<td>'.$col['it_model_no'].'</td>';
  echo '<td>'.$col['unit_price'].'</td>';
  echo '<td>'.$col['qty'].'</td>';
  echo '<td>'.$col['amount'].'</td>';
  echo '<td>'.$col['vat'].'</td>';
  echo '<td>'.$col['amount_vat'].'</td>';
  echo '</tr>';
}
echo '</table>';
?>