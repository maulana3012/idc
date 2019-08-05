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

//ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//DELETE
if(ckperm(ZKP_DELETE, "javascript:window.close();", 'delete')) {
	$_idx = $_POST['_idx'];
	$sql = "DELETE FROM ".ZKP_SQL."_tb_sales_log WHERE sl_idx = $_idx";
	if (isZKError($result = query($sql))) $M->goErrorPage($result, "javascript:window.close();");
	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
}

if(ckperm(ZKP_UPDATE, "javascript:window.close();", 'update')) {
	$_idx		= $_POST['_idx'];
	$_cus_to_responsible_by  = $_POST['_cus_to_responsible_by'];
	$_qty		= $_POST['_qty'];
	$_faktur_no	= (strlen($_POST['_faktur_no']) == 0) ? 'null' : "$\${$_POST['_faktur_no']}$\$";
	$_lop_no	= (strlen($_POST['_lop_no']) == 0) ? 'null' : "$\${$_POST['_lop_no']}$\$";
	$_payment_price = $_POST['_payment_price'];

	$sql = "UPDATE ".ZKP_SQL."_tb_sales_log SET 
				sl_qty=$_qty, 
				sl_payment_price=$_payment_price, 
				sl_faktur_no=$_faktur_no, 
				sl_lop_no=$_lop_no, 
				sl_cus_to_responsible_by=$_cus_to_responsible_by 
			WHERE sl_idx = $_idx";
	if (isZKError($result = query($sql))) $M->goErrorPage($result, "javascript:window.close();");
	else goPage($_SERVER['PHP_SELF']."?idx=$_idx");
	exit;
}

//SET PAGE PARAMETER
if(!isset($_GET['idx']) || $_GET['idx'] == '')
	die("<script language=\"javascript1.2\">window.close();</script>");

$idx = $_GET['idx'];

$sql = "
SELECT
 cus.cus_code,
 cus.cus_full_name,
 it.it_code,
 it.it_model_no,
 it.it_desc,
 sl_idx,
 sl_faktur_no,
 sl_lop_no,
 sl_payment_price,
 sl_qty,
 to_char(sl_date, 'dd-Mon-YY') AS sl_date,
 sl_cus_to_responsible_by
FROM
 ".ZKP_SQL."_tb_customer AS cus JOIN ".ZKP_SQL."_tb_sales_log AS sl USING(cus_code)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE 
 sl_idx = $idx";

if (isZKError($result =& query($sql))) $M->goErrorPage($result, "javascript:window.close();");
$sl =& fetchRowAssoc($result);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>DETAIL SALES LOG</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="javascript1.2" type="text/javascript" src="../../_script/aden.js"></script>
<script language='text/javascript' type='text/javascript'>
<?php
$sql = "SELECT ma_idx, ma_account, ma_display_as FROM tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
$result = & query($sql);
echo "var mkt = new Array();\n";
$i = 0;
while ($row =& fetchRow($result,0)) {
	if($row[2] & 1) $j='IDC';
	if($row[2] & 2) $j='MED';
	if($row[2] & 1 && $row[2] & 2) $j='ALL';
	if($row[2] == 4) $j=false;
	if($j != false) {
		if(ZKP_SQL == $j || $j == 'ALL') echo "mkt['".$i++."'] = ['".$row[0]."','".strtoupper($row[1])."',".$row[2]."];\n";
	}
}
?>

function initOption() {
	for (i=0; i<mkt.length; i++) 
		addOption(document.frmUpdate.cboFilterMarketing,mkt[i][1], mkt[i][0]);
	setSelect(window.document.frmUpdate.cboFilterMarketing, "<?php echo isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all"?>");
}
</script>
</head>
<body style="margin:8pt" onLoad="initOption()">
<table width="100%" cellpadding="0">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting_mini.gif">&nbsp;&nbsp;<strong>Detail sales log</strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table><br />
<form name="frmUpdate" action="p_detail_sales_log.php" method="post">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_idx" value="<?php echo $sl['sl_idx']?>">
<table width="100%" class="table_l">
	<tr>
		<th width="25%">CUSTOMER</th>
		<td colspan="3"><?php echo "<b>[". trim($sl['cus_code']) ."]</b> ".$sl['cus_full_name']?></td>
	</tr>
	<tr>
		<th>MODEL</th>
		<td colspan="3"><?php echo "<b>[{$sl['it_code']}] </b> {$sl['it_model_no']}"?></td>
	</tr>
	<tr>
		<th>FAKTUR NO</th>
		<td><input type="text" name="_faktur_no" class="fmt" style="width:100%" maxlength="16" value="<?php echo $sl['sl_faktur_no']?>" readonly></td>
		<th>LOP NO</th>
		<td><input type="text" name="_lop_no" class="fmt" size="15" maxlength="10" value="<?php echo $sl['sl_lop_no']?>" readonly></td>
	</tr>
	<tr>
		<th>SALES DATE</th>
		<td width="35%"><?php echo $sl['sl_date']?></td>
		<th width="15%">QTY</th>
		<td><input type="text" name="_qty" class="reqn" size="5" value="<?php echo $sl['sl_qty']?>" onKeyUp="formatNumber(this, 'dot');"></td>
	</tr>
	<tr>
		<th>REPORT PRICE</th>
		<td>Rp. <input type="text" name="_payment_price" class="reqn" size="10" value="<?php echo number_format((double)$sl['sl_payment_price'])?>" onKeyUp="formatNumber(this, 'dot');"></td>
		<th>MARKETING</th>
		<td colspan="3">
        	<select name="_cus_to_responsible_by" id="cboFilterMarketing" class="fmt">
                <option value="all">==SELECT==</option>
            </select>
		</td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><button name='btnDelete' class='input_red' style='width:60px;height:30px'><img src="../../_images/icon/trash.gif" width="15px" align="middle"></button></td>
		<td align="right">
			<button name='btnUpdate' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/update.gif" align="middle"></button>&nbsp;
			<button name='btnClose' class='input_sky' style='width:60px;height:30px' onClick="window.close();"><img src="../../_images/icon/delete_2.gif" style="width:17px" align="middle"></button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {
		if(oForm._cus_to_responsible_by.value == 0) {
			alert("Responsibly by must be entered");
			return;
		}
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnClose.onclick = function() {
		window.opener.location.reload();
		window.close();
	}
</script>
</body>
</html>