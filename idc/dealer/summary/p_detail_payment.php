<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 29-May, 2007 23:50:32
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//Check PARAMETER
if(!isset($_GET['_code']) && $_GET['_code'] == '')
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code	 = $_GET['_code'];

//PROCESS DELETE PAYMENT
if (ckperm(ZKP_DELETE, HTTP_DIR . "javascript:window.close();", 'delete')) {

	$_pay_idx	= $_POST['_pay_idx'];

	$result = query("DELETE FROM ".ZKP_SQL."_tb_payment WHERE pay_idx=$_pay_idx;DELETE FROM ".ZKP_SQL."_tb_deposit WHERE pay_idx=$_pay_idx");

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "p_detail_payment.php?_code=$_code");

	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
}


//DEFAULT PROCESS
$sql = "
SELECT
  b.bill_code,
  b.bill_cus_to_name,
  b.bill_ship_to_name,
  b.bill_total_billing,
  p.pay_idx,
  p.pay_date,
  p.pay_paid,
  p.pay_remark,
  p.pay_inputed_by,
  p.pay_is_deposit_cross
FROM 
  ".ZKP_SQL."_tb_billing AS b
  JOIN ".ZKP_SQL."_tb_payment AS p USING(bill_code)
WHERE p.pay_idx = $_code";

if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");

$column =& fetchRowAssoc($result);
?>
<html>
<head>
<title>RETURN DETAIL</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {
	if(o._is_deposit_cross.value=='t') {
		alert("This payment already use for cross transfer.\n" +
			  "Delete the cross transfer if you want to delete this payment");
		window.document.all.btnDelete.disabled = true;
		return;
	}	
	if(confirm("Are you sure to delete payment?")) {
		o.submit();
	}
}
</script>
</head>
<body style="margin:8pt">
<!--START: BODY-->
<table width="100%" class="table_box">
	<tr>
		<td>
			<strong>
			<font color="black">
			[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] DETAIL PAYMENT<br />
			</font>
			</strong>
		</td>
	</tr>
</table><hr>
<table width="100%" class="table_box">
	<tr>
		<th width="20%">BILL CODE</th>
		<td><b><?php echo $column['bill_code'] ?></b></td>
		<th width="25%">TOTAL BILLING</th>
		<td>Rp. <?php echo number_format((double)$column['bill_total_billing'],2) ?></td>
	</tr>
	<tr>
		<th>CUS TO</th>
		<td colspan="3"><?php echo $column['bill_cus_to_name'] ?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<td colspan="3"><?php echo $column['bill_ship_to_name'] ?></td>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr>
		<th rowspan="3" width="25%">PAID</th>
		<th width="18%">DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column['pay_date'])) ?></td>
		<th width="18%">AMOUNT</th>
		<td>Rp. <?php echo number_format((double)$column['pay_paid'],2) ?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><?php echo $column['pay_remark'] ?></td>
	</tr>
	<tr>	
		<th>INPUTED BY</th>
		<td colspan="3"><?php echo $column['pay_inputed_by'] ?></td>
	</tr>
</table>
<form name="frmPayment" method="post">
<input type="hidden" name="p_mode" value="delete">
<input type="hidden" name="_pay_idx" value="<?php echo $column['pay_idx'] ?>">
<input type="hidden" name="_is_deposit_cross" value="<?php echo $column['pay_is_deposit_cross'] ?>">
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><button name='btnDelete' class='input_btn' style='width:100px;' onClick="checkform(window.document.frmPayment)"><img src="../../_images/icon/trash.gif" align="middle"> &nbsp; Delete</button></td>
		<td align="right">
			<button name='btnClose' class='input_btn' style='width:100px;' onClick="window.close()"><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"> &nbsp; Close</button>
		</td>
</table>
<!--END: BODY-->
</body>
</html>