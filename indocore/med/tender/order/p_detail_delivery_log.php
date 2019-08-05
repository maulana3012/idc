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
if(ckperm(ZKP_DELETE, 'javascript:window.close();', 'delete')) {
	$_idx = $_POST['_idx'];
	$_it_code = $_POST['_it_code'];

	$sql = "DELETE FROM ".ZKP_SQL."_tb_delivery_item WHERE deli_idx = {$_idx} AND it_code = '{$_it_code}'";
	if (isZKError($result = query($sql))) $M->goErrorPage($result, "javascript:window.close();");
	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");

}

if(ckperm(ZKP_UPDATE, 'javascript:window.close();', 'update')) {
	$_idx      = $_POST['_idx'];
	$_it_code  = $_POST['_it_code'];
	$_deit_qty = $_POST['_deit_qty'];
	$_ord_type = $_POST['_ord_type'];

	if ($_ord_type == "OO")
		$sql = "UPDATE ".ZKP_SQL."_tb_delivery_item SET deit_jo_qty=$_deit_qty, deit_qty=$_deit_qty WHERE deli_idx={$_idx} AND it_code='{$_it_code}'";
	else
		$sql = "UPDATE ".ZKP_SQL."_tb_delivery_item SET deit_jk_qty=$_deit_qty, deit_qty=$_deit_qty WHERE deli_idx={$_idx} AND it_code='{$_it_code}'";

	if (isZKError($result = query($sql))) $M->goErrorPage($result, "javascript:window.close();");
	else goPage($_SERVER['PHP_SELF']."?idx={$_idx}&_code={$_it_code}");
	exit;
}

//SET PAGE PARAMETER
if(!isset($_GET['idx']) || $_GET['idx'] == '')
	die("<script language=\"javascript1.2\">window.close();</script>");

if(!isset($_GET['_code']) || $_GET['_code'] == '')
	die("<script language=\"javascript1.2\">window.close();</script>");

$idx	= $_GET['idx'];
$_code	= trim(urldecode($_GET['_code']));

$sql = "
SELECT
  ord.ord_code,
  ord.ord_type,
  to_char(ord.ord_po_date, 'dd-Mon-YYYY') as po_date,
  ord.ord_ship_to,
  ord.ord_ship_to_attn,
  deit.it_code,
  it.it_model_no,
  deli.deli_idx,
  (SELECT odit_qty FROM ".ZKP_SQL."_tb_order_item WHERE ord_code = ord.ord_code AND it_code = deit.it_code) AS ord_qty,
  deit.deit_qty
FROM
  ".ZKP_SQL."_tb_order AS ord JOIN ".ZKP_SQL."_tb_delivery AS deli USING(ord_code)
  JOIN ".ZKP_SQL."_tb_delivery_item AS deit USING(deli_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE
	deit.deli_idx = {$idx} AND deit.it_code = '{$_code}'
";

if (isZKError($result =& query($sql))) $M->goErrorPage($result, "javascript:window.close();");
$deit =& fetchRowAssoc($result);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>DETAIL DELIVERY LOG</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="javascript1.2" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<table width="100%" cellpadding="0">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting_mini.gif">&nbsp;&nbsp;<strong>Detail delivery log</strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table><br />
<form name="frmUpdate" action="p_detail_delivery_log.php" method="post">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_idx" value="<?php echo $deit['deli_idx']?>">
<input type="hidden" name="_it_code" value="<?php echo $deit['it_code']?>">
<input type="hidden" name="_ord_type" value="<?php echo $deit['ord_type']?>">
<table width="100%" class="table_c">
	<tr>
		<th>ORDER#</th>
		<td><?php echo $deit['ord_code']?></td>
		<th>PO DATE</th>
		<td><?php echo $deit['po_date']?></td>
	</tr>
	<tr>
		<th width="20%">SHIP TO</th>
		<td colspan="3"><?php echo "[". $deit['ord_ship_to']. "] " . $deit['ord_ship_to_attn']?></td>
	</tr>
	<tr>
		<th>MODEL</th>
		<td colspan="3"><?php echo "[{$deit['it_code']}] {$deit['it_model_no']}"?></td>
	</tr>
	<tr>
		<th>ORD QTY</th>
		<td><?php echo $deit['ord_qty']?></td>
		<th>DELI QTY</th>
		<td><input type="text" name="_deit_qty" class="reqn" size="5" value="<?php echo $deit['deit_qty']?>"></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><button name='btnDelete' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/trash.gif" width="15px" align="middle"></button></td>
		<td align="right">
			<button name='btnUpdate' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/update.gif" align="middle"></button>
			<button name='btnClose' class='input_sky' style='width:60px;height:30px' onClick="window.close();"><img src="../../_images/icon/close.gif"></button>
		</td>
	</tr>
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