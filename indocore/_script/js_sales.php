<?php
// index
// 0  => Customer Group Code
// 1  => Customer Group Name
// 2  => Customer Code
// 3  => Customer Name
// 4  => Sales Date
// 5  => Item Code
// 6  => Model No
// 7  => Desc
// 8  => Sales Idx
// 9  => Unit Price
// 10 => Qty
// 11 => Total Price
// 12 => Faktur No
// 13 => LOP No
// 14 => Date [to order date period]
// 15 => idx
//REQUIRE
require_once "../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//SET PARAMETER
$_cug_code 	= trim($_GET['_cug_code']);
$_dept 		= $_GET['_dept'];
$_ship_to 	= $_GET['_ship_to'];
if($_dept == 'A') {
	$tmp[] = "cug_code = '$_cug_code' AND sl_dept='$_dept'";
	$tmp[] = "sl_date between date '".date("M-d-Y", mktime(0, 0, 0, date('m')-6, date('d'), date('Y'))). "' and '" . date("M-d-Y") . "'";
	$strWhere = implode(" AND ", $tmp);
	$sql = "
	select
	  cug_code, cug_name, cus_code, cus_full_name, 
	  to_char(sl_date, 'dd-Mon-yy') AS sl_date,
	  it_code, it_model_no, it_desc, sl_idx, 
	  sl_payment_price AS price,
	  sl_qty AS qty,
	  (sl_payment_price*sl_qty)+round(((sl_payment_price*sl_qty)/10),0) AS amount,
	  sl_faktur_no,
	  sl_lop_no,
	  sl_date AS date
	FROM
	  ".ZKP_SQL."_tb_customer_group 
	  join ".ZKP_SQL."_tb_customer using(cug_code)
	  join ".ZKP_SQL."_tb_sales_log using(cus_code)
	  join ".ZKP_SQL."_tb_item using(it_code)
	WHERE $strWhere
	ORDER BY cus_code, it_code, date 
	";
} else {
	$tmp[] = "cus_code='$_ship_to' AND sl_dept='$_dept'";
	$tmp[] = "sl_date between date '".date("M-d-Y", mktime(0, 0, 0, date('m')-6, date('d'), date('Y'))). "' and '" . date("M-d-Y") . "'";
	$strWhere = implode(" AND ", $tmp);
	$sql = "
	select
	  NULL AS cug_code, NULL AS cug_name, cus_code, cus_full_name, 
	  to_char(sl_date, 'dd-Mon-yy') AS sl_date,
	  it_code, it_model_no, it_desc, sl_idx, 
	  sl_payment_price AS price,
	  sl_qty AS qty,
	  (sl_payment_price*sl_qty)+round(((sl_payment_price*sl_qty)/10),0) AS amount,
	  sl_faktur_no,
	  sl_lop_no,
	  sl_date AS date
	FROM
	  ".ZKP_SQL."_tb_customer
	  join ".ZKP_SQL."_tb_sales_log using(cus_code)
	  join ".ZKP_SQL."_tb_item using(it_code)
	WHERE $strWhere
	ORDER BY cus_code, it_code, date 
	";
}

$result =& query($sql);
echo "var sl = new Array();\n";
$i = 0;
while ($rows =& fetchRow($result,0)) {
	$rows[] = $i;
	foreach($rows as $key => $val) {
		$rows[$key] = trim(addslashes($val));
	}
	$element = implode("', '", $rows);
	echo "sl[".$i."] = ['".$element."'];\n";
	$i++;
}
?>