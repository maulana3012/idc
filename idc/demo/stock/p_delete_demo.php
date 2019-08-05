<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_UPDATE, "javascript:window.close();");
/*
//Check PARAMETER
if(!isset($_GET['_it_code']) || $_GET['_it_code']=='' )
	die("<script language=\"javascript1.2\">window.close();</script>");*/
$_it_code	 = $_GET['_it_code'];

//=============================================================================================== move stock
if(ckperm(ZKP_DELETE, "javascript:window.close();", 'delete')) {

	$_it_code 	= $_POST['_it_code'];
	$_it_desc 	= $_POST['_it_desc'];
	$_it_qty 	= $_POST['_it_qty'];
	$_log_by_account	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_deleteDemoStock",
		"$\${$_it_code}$\$",
		null,
		"$\${$_it_desc}$\$",
		$_it_qty,
		"$\${$_log_by_account}$\$"
	);

	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
}

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT it_code, it_model_no, it_desc, demo_qty FROM ".ZKP_SQL."_tb_item JOIN ".ZKP_SQL."_tb_demo USING(it_code) WHERE it_code = '$_it_code'";
$result = query($sql);
$column = fetchRowAssoc($result);
?>
<html>
<head>
<title>DETAIL REJECT</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script type="text/javascript">
function checkform(o) {
	if(verify(o)){
		if(o._it_qty.value.length <= 0 || parseFloat(parseInt(removecomma(o._it_qty.value))) <= 0) {
			alert("Deleted qty have to more than zero");
			o._it_qty.focus();
			return;
		} else if(parseFloat(parseInt(removecomma(o._it_qty.value))) > parseFloat(parseInt(removecomma(o._max_qty.value)))) {
			alert("Deleted qty cannot more than demo qty");
			o._it_qty.value = o._max_qty.value;
			o._it_qty.focus();
			return;
		}
	
		if(confirm("Are you sure to update?")) {
			o.submit();
		}
	}
}
</script>
</head>
<body style="margin:8pt">
<table width="100%" cellpadding="0" class="main">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting.gif"> &nbsp; <strong>DELETE DEMO STOCK</strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table>
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Item Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
<form name="frmSearch" method="GET">
<input type='hidden' name='_it_code' value='<?php echo $column['it_code'] ?>'>
<table width="100%" class="table_l">
	<tr>
		<th width="20%">ITEM</th>
		<td colspan="2"><strong class="info"><font color="#446fbe" style="font-weight:bold">[<?php echo trim($column['it_code']) ?>]</font> <?php echo $column['it_model_no'] ?></strong></td>
	</tr>
	<tr>
		<th>DESC</th>
		<td><?php echo $column['it_desc'] ?></td>
	</tr>
</table>
</form>
    	</td>
    </tr>
    <tr height="10">	
    	<td></td>
    </tr>
    <tr>
		<td valign="top"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
    	<td>
<form name="frmInsert" method="POST">
<input type='hidden' name='p_mode' value='delete'>
<input type='hidden' name='_it_code' value='<?php echo $column['it_code'] ?>'>
<strong>Demo Information</strong>
<table width="100%" class="table_l">
	<tr>
		<th width="20%">Balance</th>
		<td width="30%"><input type="text" name="_max_qty" class="reqn" style="width:60%" value="<?php echo $column['demo_qty'] ?>" readonly></td>
		<th width="20%">Deleted Qty</th>
		<td><input type="text" name="_it_qty" class="reqn" style="width:60%" value="0" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) checkform(window.document.frmInsert)"></td>
	</tr>
	<tr>
		<th>Desc</th>
		<td colspan="3"><input type="text" name="_it_desc" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode == 13) checkform(window.document.frmInsert)"></td>
	</tr>
</table>
<div align="right">
	<button name='btnUpdate' class='input_btn' style='width:100px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/setting_mini.gif" align="middle" alt="Update qty"> &nbsp; Update</button>&nbsp;
	<button name='btnClose' class='input_btn' style='width:100px;' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"> &nbsp; Close</button>
</div>
</form>
    	</td>
    </tr>
</table>
</body>
</html>