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

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
require_once "./tpl_process_form.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//PROCESS FORM
require_once "tpl_process_form.php"; 

//CHECK PARAMETER
if(!isset($_GET['_code']) || $_GET['_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code	  = $_GET['_code'];
$_code	  = explode(",",trim($_code));
$_code   = "'".implode("','",$_code)."'";
$doc_name = array();

//DEFAULT PROCESS
$doc_sql = "SELECT bill_code FROM ".ZKP_SQL."_tb_billing WHERE bill_code IN ($_code)";

$sql = "
SELECT
  bill_pajak_to_name AS cus_full_name,
  bill_code,
  to_char(bill_inv_date,'dd/Mon/YY') AS inv_date,
  bill_npwp,
  bill_is_fp_delivery,
  bill_vat_inv_no,
  cus_code,
  bill_ship_to,
  bill_pajak_to,
  ".ZKP_SQL."_getemail(bill_pajak_to, bill_ship_to) AS email_pajak,
  CASE 
  	WHEN '".ZKP_URL."' = 'MED' AND bill_pajak_to = '0MSD' THEN 
  		TRUNC(((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100)) * 0.888888888888888,0)
  	ELSE (bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) 
  END AS amount,
  CASE 
  	WHEN '".ZKP_URL."' = 'MED' AND bill_pajak_to = '0MSD' THEN 
  		TRUNC(TRUNC(((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100)) * 0.888888888888888,0)*0.1,0)
  	ELSE
  		(bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) * bill_vat/100 
  END AS vat,
  CASE 
  	WHEN '".ZKP_URL."' = 'MED' AND bill_pajak_to = '0MSD' THEN 
  		TRUNC(bill_total_billing * 0.888888888888888,0)- bill_delivery_freight_charge
  	ELSE
  		bill_total_billing - bill_delivery_freight_charge 
  END AS amount_vat,
  CASE 
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'A' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'D' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'H' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'M' THEN 'putri.marketing@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'P' THEN 'ati.apotik@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'IDC' AND bill_dept = 'T' THEN 'putri.marketing@indocore.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'A' THEN 'dewi.apotik@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'D' THEN 'linda.dealer@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'H' THEN 'nuri.hospital@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 AND bill_dept = 'T' THEN 'sarah.bs@medisindo.co.id'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 2 AND bill_dept = 'T' THEN 'sarah.bs@medisindo.co.id'
  END AS email_admin
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON  bill_cus_to = cus_code
WHERE bill_code IN ($_code)
ORDER BY bill_code
";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
$result =& query($doc_sql);
while($column =& fetchRowAssoc($result)) {
	$doc_name[] = $column["bill_code"];
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body style="margin:10pt" onload="window.document.frmSendEmail.btnSend.focus()">
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] SEND MAIL FAKTUR PAJAK</h4>
<form method="post" name="frmSendEmail">
<input type="hidden" name="p_mode">
<input type="hidden" name="_bill_code" value= "<?php echo $_GET['_code']; ?>">
<input type="hidden" name="_file_type" value= "FP">
<input type="hidden" name="_source" value= "popup">
<input type="hidden" name="_location" value="p_summary_faktur_pajak.php?_code=<?php echo $_GET['_code'] ?>">
<table width="100%" class="table_nn">
	<tr height="30">
		<th width="3%">NO</th>
		<th width="15%">FP NO</th>
		<th width="12%">INV NO</th>
		<th width="10%">INV DATE</th>
		<th width="25%">CUSTOMER</th>
		<th width="10%">AMOUNT</th>
		<th width="8%">VAT</th>
		<th width="10%">AMOUNT + VAT</th>
	</tr>
</table>
<div style="height:350; overflow-y:scroll">
<table width="100%" class="table_nn">
<?php
$i = 1;
$is_same = true;
$cus = "";
$result =& query($sql);
$data = array();
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td width="3%" align="center"><?php echo $i++ ?></td>
		<td width="15%" align="center"><?php echo $column['bill_vat_inv_no']; ?></td>
		<td width="12%" align="center"><?php echo $column['bill_code']; ?></td>
		<td width="10%" align="center"><?php echo $column['inv_date']; ?></td>
		<td width="25%" align="left"><?php echo $column['cus_full_name']; ?></td>
		<td width="10%" align="right"><?php echo number_format((double)$column['amount']); ?></td>
		<td width="8%" align="right"><?php echo number_format((double)$column['vat']); ?></td>
		<td width="10%" align="right"><?php echo number_format((double)$column['amount_vat']); ?></td>
	</tr>
<?php
	if($i > 2) {
		if(substr(trim($column["bill_pajak_to"]),0,2) == '2F') {
			if($cus != trim($column["bill_pajak_to"]) . $column["bill_ship_to"]) {
				$is_same = false;
			}
		} else {
			if($cus != trim($column["bill_pajak_to"])) {
				$is_same = false;
			}
		}
	}
	
	if(substr(trim($column["bill_pajak_to"]),0,2) == '2F') {
		$cus = trim($column["bill_pajak_to"]) . $column["bill_ship_to"];
	} else {
		$cus = trim($column["bill_pajak_to"]);
	}

	$data["customer"] = $column["cus_full_name"];
	$data["email"] = $column["email_pajak"];
	$data["email_admin"] = $column["email_admin"];	
}
?>
</table>
</div>
<br />
<?php 
if(!$is_same) {
	echo "<script>window.alert('Customer Must Be Same !');</script>";
	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
}
?>
<table width="100%" class="table_nn">
	<tr>
		<th width="20%">CUSTOMER</th>
		<td>
			<b><?php echo $data["customer"] ?></b> (<?php echo numQueryRows($result) ?> faktur)
			<input type="hidden" name="_customer_name" value= "<?php echo $data["customer"] ?>">
		</td>
		<td width="25%" rowspan="3" align="center">
			<button name="btnSend" class="input_red" style="width:60%; height:50px">SEND MAIL</button> &nbsp;
			<button name="btnClose" class="input_sky" style="width:30%; height:50px">Close</button>
		</td>
	</tr>
	<tr>
		<th>EMAIL CUSTOMER ( to: )</th>
		<td>
			<?php echo $data["email"] ?>
			<input type="hidden" name="_customer_email" value= "<?php echo $data["email"] ?>">
		</td>
	</tr>
	<tr>
		<th>EMAIL INTERNAL ( cc: )</th>
		<td>
			<?php echo $data["email_admin"] ?>
			<input type="hidden" name="_internal_email" value= "<?php echo $data["email_admin"] ?>">
		</td>
	</tr>
</table>
</form>
<script language='text/javascript' type='text/javascript'>
oForm = window.document.frmSendEmail;

oForm.btnSend.onclick = function() {
    if(confirm("Are you sure to send email from these invoice(s)?")) {
        oForm.p_mode.value = "send_mail";
        oForm.submit();
    }
}

oForm.btnClose.onclick = function() {
    window.close();
}
</script>
</body>
</html>