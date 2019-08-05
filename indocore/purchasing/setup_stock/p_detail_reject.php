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

//Check PARAMETER
if(!isset($_GET['_idx']) || $_GET['_idx']=='' )
	die("<script language=\"javascript1.2\">window.close();</script>");
$_idx	 = $_GET['_idx'];
$_del	 = $_GET['btnDel'];

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT * FROM ".ZKP_SQL."_tb_reject_item AS a JOIN ".ZKP_SQL."_tb_item AS b USING(it_code) WHERE rjit_idx = $_idx";
$result = query($sql);
$column = fetchRowAssoc($result);

//========================================================================================= unconfirm DO
if(ckperm(ZKP_DELETE, "javascript:window.close();", 'delete')) {

	$_idx			= $_POST["_idx"];
	$_it_code		= $_POST["_it_code"];
	$_it_location	= $_POST["_it_location"];
	$_it_type		= $_POST["_it_type"];
	$_log_by_account = $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_deleteRejectStock",
		$_idx,
		"$\${$_it_code}$\$",
		$_it_location,
		$_it_type,
		"$\${$_log_by_account}$\$"
	);
	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/p_detail_reject.php?_idx=$_idx");
	}
	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
}

//---------------------------------------------------------------------------------------------- update reject
if(ckperm(ZKP_UPDATE, HTTP_DIR . $currentDept . "javascript:window.close();", 'update')) {

	$_idx			= $_POST["_idx"];
	$_it_code		= $_POST["_it_code"];
	$_it_serial_no	= $_POST["_it_serial_no"];
	$_it_warranty	= $_POST["_it_warranty"];
	$_desc			= $_POST["_it_desc"];
	if(isset($_POST["_is_replace"])) {
 		$_is_replace	= ($_POST["_is_replace"] == 't') ? "true" : "false";
	} else {
		$_is_replace	= "true";
	}
	$_it_replacement= $_POST["_it_replacement"];
	$_it_status		= $_POST["_it_status"];
	$_log_by_account = $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_updateRejectStock",
		$_idx,
		"$\${$_it_code}$\$",
		"$\${$_it_serial_no}$\$",
		"$\${$_it_warranty}$\$",
		"$\${$_desc}$\$",
		"$\${$_is_replace}$\$",
		$_it_replacement,
		"$\${$_it_status}$\$",
		"$\${$_log_by_account}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/p_detail_reject.php?_idx=$_idx");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/p_detail_reject.php?_idx=$_idx");
}
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
function enabledRadio(value) {
	var f = window.document.frmUpdate;

	if(value) {
		if(f._it_replacement[0].checked) {
			f._it_status[2].disabled = true;
		} else if(f._it_replacement[1].checked) {
			f._it_status[2].disabled = false;
		} else {
			f._it_replacement[0].checked = true;
			f._it_status[2].disabled = true;
		}
		f._it_status[1].checked			= true;
		f._it_replacement[0].disabled	= false;
		f._it_replacement[1].disabled	= false;
		f._it_status[0].disabled		= true;
		f._it_status[1].disabled		= false;
		f._it_status[3].disabled		= false;
	} else {
		f._it_status[0].checked			= true;
		f._it_replacement[0].checked	= false;
		f._it_replacement[1].checked	= false;
		f._it_replacement[0].disabled	= true;
		f._it_replacement[1].disabled	= true;
		f._it_status[0].disabled		= false;
		f._it_status[1].disabled		= true;
		f._it_status[2].disabled		= true;
		f._it_status[3].disabled		= true;
	}
	defaultValue();
}

function defaultValue() {
	var f = window.document.frmUpdate;
	if(f._it_status[1].checked) {
		f._is_replace[0].disabled	= true;
		f._is_replace[1].disabled	= true;
		f._it_status[0].disabled	= true;

		if(f._it_replacement[0].checked) {
			f._it_status[2].disabled	= true;
		} else if(f._it_status[1].checked && f._it_replacement[1].checked) {
			f._it_status[2].disabled	= false;
		}
	}
}

function initPage(){
	var f = window.document.frmUpdate;

<?php if($_del == 'true') { ?>
	window.document.all.btnDelete.disabled = true;
<?php } ?>

<?php if($column["rjit_is_replace"]=='f') { ?>
	f._it_replacement[0].disabled	= true;
	f._it_replacement[1].disabled	= true;
	f._it_status[1].disabled		= true;
	f._it_status[2].disabled		= true;
	f._it_status[3].disabled		= true;
<?php } ?>

<?php if($column["rjit_status"]=='on_stock' || $column["rjit_status"]=='on_deleted') { ?>
	window.document.all.btnDelete.disabled = true;
	window.document.all.btnUpdate.disabled = true;
	f._is_replace[0].disabled	= true;
	f._is_replace[1].disabled	= true;
	f._it_replacement[0].disabled	= true;
	f._it_replacement[1].disabled	= true;
	f._it_status[0].disabled		= true;
	f._it_status[1].disabled		= true;
	f._it_status[2].disabled		= true;
	f._it_status[3].disabled		= true;
<?php } ?>
	defaultValue();
}
</script>
</head>
<body style="margin:8pt" onload="initPage()">
<table width="100%" cellpadding="0" class="main">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting.gif"> &nbsp; <strong>DETAIL REJECT STOCK</strong></td>
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
<form name='frmUpdate' method='POST'>
<input type='hidden' name='p_mode'>
<input type="hidden" name="_idx" value="<?php echo $column["rjit_idx"] ?>">
<input type="hidden" name="_it_code" value="<?php echo $column["it_code"] ?>">
<input type="hidden" name="_it_location" value="<?php echo $column["rjit_wh_location"] ?>">
<input type="hidden" name="_it_type" value="<?php echo $column["rjit_type"] ?>">
<table width="100%" class="table_nn">
	<tr>
		<th width="20%">ITEM CODE</th>
		<td colspan="3"><span class="bar_bl"><?php echo "[<span class=\"bar_bu\">".trim($column["it_code"])."</span>] ".$column["it_model_no"] ?></span></td>
	</tr>
	<tr>
		<th>SN</th>
		<td><input type="text" name="_it_serial_no" class="req" style="width:100%" value="<?php echo $column["rjit_serial_number"] ?>"></td>
		<th width="20%">WARRANTY</th>
		<td width="20%"><input type="text" name="_it_warranty" class="reqd" style="width:100%" value="<?php echo date('d-M-Y',strtotime($column["rjit_warranty"])) ?>"></td>
	</tr>
	<tr>
		<th>DESC</th>
		<td colspan="3"><input type="text" name="_it_desc" class="fmt" style="width:100%" value="<?php echo $column["rjit_desc"] ?>"></td>
	</tr>
	<tr>
		<th>REPLACEMENT</th>
		<td>
			<input type="radio" name="_is_replace" value="f" id="y" onclick="enabledRadio(false)"<?php echo ($column["rjit_is_replace"]=='f')?' checked':'' ?>><label for="y"> No &nbsp;</label>
			<input type="radio" name="_is_replace" value="t" id="n" onclick="enabledRadio(true)"<?php echo ($column["rjit_is_replace"]=='t')?' checked':'' ?>><label for="n"> Yes &nbsp; &nbsp;</label>
		</td>
		<td colspan="2">
			<input type="radio" name="_it_replacement" value="0" id="whole" onclick="enabledRadio(true)"<?php echo ($column["rjit_replace_item"]=='0')?' checked':'' ?>><label for="whole"> Whole &nbsp; </label>
			<input type="radio" name="_it_replacement" value="1" id="part" onclick="enabledRadio(true)"<?php echo ($column["rjit_replace_item"]=='1')?' checked':'' ?>><label for="part"> Spare Part </label>
		</td>
	</tr>
	<tr>
		<th>STATUS</th>
		<td colspan="3">
			<input type="radio" name="_it_status" value="on_wh" id="on_wh"<?php echo ($column["rjit_status"]=='on_wh')?' checked':'' ?>><label for="on_wh"> In Warehouse &nbsp; </label>
			<input type="radio" name="_it_status" value="on_repair" id="on_repair"<?php echo ($column["rjit_status"]=='on_repair')?' checked':'' ?>><label for="on_repair"> Repaired &nbsp; </label>
			<input type="radio" name="_it_status" value="on_stock" id="on_stock"<?php echo ($column["rjit_status"]=='on_stock')?' checked':'' ?>><label for="on_stock"> Back to Stock &nbsp; </label>
			<input type="radio" name="_it_status" value="on_deleted" id="on_deleted"<?php echo ($column["rjit_status"]=='on_deleted')?' checked':'' ?>><label for="on_deleted"> Deleted &nbsp; </label>
		</td>
	</tr>
</table>
</form>
    	</td>
    </tr>
</table>
<!--START Button-->
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><button name='btnDelete' class='input_btn' style='width:100px;'><img src="../../_images/icon/trash.gif" align="middle" alt="Delete reject item"> &nbsp; Delete</button></td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:100px;'><img src="../../_images/icon/setting_mini.gif" align="middle" alt="Update reject item"> &nbsp; Update</button>&nbsp;
			<button name='btnClose' class='input_btn' style='width:100px;'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"> &nbsp; Close</button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'delete';
				oForm.submit();
			}
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