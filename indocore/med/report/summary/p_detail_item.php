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

//SET PAGE PARAMETER
if(!isset($_GET['_code']) || $_GET['_code'] == '')
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code = $_GET['_code'];

//DELETE
if(ckperm(ZKP_UPDATE, $_SERVER['PHP_SELF']."?_code=$_code", 'update')) {
	$_code		= $_POST['_code'];
	$_calc_stock_max		= $_POST['_calc_stock_max'];
	$_calc_stock_delivery	= $_POST['_calc_stock_delivery'];

	$sql = "UPDATE ".ZKP_SQL."_tb_item SET 
				it_calc_stock_max		= $_calc_stock_max,
				it_calc_stock_delivery	= $_calc_stock_delivery
			WHERE it_code = '$_code'";
	if (isZKError($result = query($sql))) $M->goErrorPage($result, "javascript:window.close();");
	else goPage($_SERVER['PHP_SELF']."?_code=$_code");
}

$sql = "SELECT * FROM ".ZKP_SQL."_tb_item WHERE it_code = '$_code'";

if (isZKError($result =& query($sql))) $M->goErrorPage($result, "javascript:window.close();");
$it =& fetchRowAssoc($result);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>DETAIL ITEM SETUP</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="javascript1.2" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<table width="100%" cellpadding="0">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting_mini.gif">&nbsp;&nbsp;<strong>Detail Item Setup</strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table><br />
<form name="frmUpdate" id="formData" action="p_detail_item.php?_code=<?php echo $it['it_code']?>" method="post" onSubmit="return submitForm();">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $it['it_code']?>">
<table width="100%" class="table_l">
	<tr>
		<th width="25%">MODEL</th>
		<td colspan="3"><?php echo "<b>[". trim($it['it_code']) ."]</b> ".$it['it_model_no']?></td>
	</tr>
    <tr>
		<th>MAX STOCK</th>
		<td><input type="text" name="_calc_stock_max" class="reqn" size="5" value="<?php echo $it['it_calc_stock_max']?>" onKeyUp="formatNumber(this, 'dot');"></td>
		<th>DELIVERY</th>
		<td><input type="text" name="_calc_stock_delivery" class="reqn" size="5" value="<?php echo $it['it_calc_stock_delivery']?>" onKeyUp="formatNumber(this, 'dot');"></td>
	</tr>
</table><br />
</form>
<div align="right">
    <button name='btnUpdate' class='input_sky' style='width:60px;height:30px'><img src="../../_images/icon/update.gif" align="middle"></button>&nbsp;
    <button name='btnClose' class='input_sky' style='width:60px;height:30px' onClick="window.close();"><img src="../../_images/icon/delete_2.gif" style="width:17px" align="middle"></button>
</div><br />
<div id="divResult" style="font-size:11px;text-align:center;display:none"></div>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;

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