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
$s_mode   = isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_cug_code  = isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_order_by  = isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][6][0];
$_chk_company = isset($_GET['chkCompany']) ? $_GET['chkCompany'] : 'off';
$_filter_doc  = isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_dept    = isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_vat   = isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
$_marketing = isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_paper   = isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$cboSearchType  = isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch  = isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

if($s_mode == 'period') {
  $some_date    = "";
  $period_from  = isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
  $period_to    = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {
  $some_date    = $_GET['some_date'];
  $period_from  = "";
  $period_to    = "";
}


include "report/rpt_daily_billing_detail_by_data_print_1.php";


//DEFAULT LIST
$sql_bill[1] .= "
  'bill-'||biit_idx as idx,
  TRIM(b.bill_ship_to) AS ship_to,
  b.bill_ship_to_name AS ship_to_name,
  b.bill_code AS invoice_code,
  to_char(b.bill_inv_date, 'dd-Mon-YY') AS invoice_issue_date,
  to_char(b.bill_inv_date, 'Mon') AS invoice_month,
  to_char(b.bill_inv_date, 'YYYY') AS invoice_year,
  to_char(b.bill_inv_date, 'YYMM') AS invoice_period,
  $$'$$||TRIM(it.it_code) AS it_code,
  it.it_model_no AS it_model_no,
  ROUND(bi.biit_unit_price * (1 - b.bill_discount/100),0)::float AS unit_price,
  bi.biit_qty::float AS qty,
  ROUND((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float AS amount,
  ROUND((b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float AS vat,
  ROUND((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float + 
  ROUND((b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),0)::float AS amount_vat,
  b.bill_inv_date AS invoice_date
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_billing_item AS bi USING(bill_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ".$strWhere[3];

$sql_return[1] .= "
  'turn-'||reit_idx as idx,
  TRIM(t.turn_ship_to) AS ship_to,
  t.turn_ship_to_name AS ship_to_name,
  t.turn_code AS invoice_code,
  to_char(t.turn_return_date, 'dd-Mon-YY') AS invoice_issued_date,
  to_char(t.turn_return_date, 'Mon') AS invoice_month,
  to_char(t.turn_return_date, 'YYYY') AS invoice_year,
  to_char(t.turn_return_date, 'YYMM') AS invoice_period,
  $$'$$||TRIM(it.it_code) AS it_code,
  it.it_model_no AS it_model_no,
  ROUND(ti.reit_unit_price * (1 - t.turn_discount/100),0)::float AS unit_price,
  (ti.reit_qty*-1)::float AS qty,
  (ROUND((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float AS amount,
  (ROUND((t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float AS vat,
  (ROUND((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float + 
  (ROUND((t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),0)*-1)::float AS amount_vat,
  t.turn_return_date AS invoice_date
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_return AS t ON t.turn_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_return_item AS ti USING(turn_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ".$strWhere[4] ;

$sql_dr[1] .= "
  'dr-'||drit_idx as idx,
  TRIM(b.dr_ship_to) AS ship_to,
  b.dr_ship_name AS ship_to_name,
  b.dr_code AS invoice_code,
  to_char(b.dr_issued_date, 'dd-Mon-YY') AS invoice_issue_date,
  to_char(b.dr_issued_date, 'Mon') AS invoice_month,
  to_char(b.dr_issued_date, 'YYYY') AS invoice_year,
  to_char(b.dr_issued_date, 'YYMM') AS invoice_period,
  $$'$$||TRIM(it.it_code) AS it_code,
  it.it_model_no AS it_model_no,
  null AS unit_price,
  (bi.drit_qty)::float AS qty,
  null AS amount,
  null AS vat,
  null AS amount_vat,
  b.dr_issued_date AS invoice_date
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_dr AS b ON dr_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_dr_item AS bi USING(dr_code)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ".$strWhere[5] ;

$sql = $sql_bill[1] ." UNION " .$sql_return[1] ." UNION ". $sql_dr[1] . " ORDER BY cug_name, ship_to, invoice_date, invoice_code, it_code";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/

print <<<END
  <table>
    <tr>
      <th>GROUP</th>
      <th>CUS CODE</th>
      <th>SHIP TO CUSTOMER</th>
      <th>DOCUMENT NO</th>
      <th>DOCUMENT DATE</th>
      <th>PERIOD</th>
      <th>YEAR</th>      
      <th>MONTH</th>
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
  echo '<tr>';
  echo '<td>'.$col['cug_name'].'</td>';
  echo '<td>'.$col['ship_to'].'</td>';
  echo '<td>'.$col['ship_to_name'].'</td>';
  echo '<td>'.$col['invoice_code'].'</td>';
  echo '<td>'.$col['invoice_issue_date'].'</td>';
  echo '<td>'.$col['invoice_period'].'</td>';
  echo '<td>'.$col['invoice_year'].'</td>';
  echo '<td>'.$col['invoice_month'].'</td>';
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