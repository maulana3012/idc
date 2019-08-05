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
ckperm(ZKP_SELECT, "javascript:window.close();");

//Check PARAMETER
if(!isset($_GET['_code']) || !isset($_GET['_type']) || !isset($_GET['_location']))
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code	 = trim($_GET['_code']);
$_type	 = trim($_GET['_type']);
$_location	 = trim($_GET['_location']);

//---------------------------------------------------------------------------------------------- delete initial 
if(ckperm(ZKP_DELETE, HTTP_DIR . "javascript:window.close()", 'unconfirmed')) {

	$_code		  = $_POST['_it_code'];
	$_it_location = $_POST['_it_location'];
	$_it_type	  = $_POST['_it_type'];
	$_remark	  = $_POST['_remark'];
	$_log_by	  = $S->getValue('ma_account');

	$result = executeSP(
		ZKP_SQL."_unconfirmedInitialStock",
		"$\${$_code}$\$",
		$_it_location,
		$_it_type,
		"$\${$_remark}$\$",
		"$\${$_log_by}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/p_detail_initial.php?_code=$_code&_type=$_type&_location=$_location");
	}
	die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
}

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT * FROM ".ZKP_SQL."_tb_initial_stock_v2 AS a JOIN ".ZKP_SQL."_tb_item AS b USING(it_code) WHERE it_code = '$_code' AND init_type = $_type AND init_wh_location = $_location";
$result = query($sql);
$column = fetchRowAssoc($result);

$sql_ed = "SELECT * FROM ".ZKP_SQL."_tb_initial_stock_ed WHERE it_code = '$_code' AND ined_wh_location = $_location ORDER BY ined_expired_date";
$res_ed = query($sql_ed);
?>
<html>
<head>
<title>DETAIL INITIAL</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function updateAmount(){
<?php if($column["it_ed"]=='t') {?>
	var e 			= window.document.all.elements;
	var count		= window.EDPosition.rows.length;
	var sumOfQty	= 0;

	for (var i=0; i<count; i++) {
		var oRow	= window.EDPosition.rows(i);
		sumOfQty	+= parseFloat(removecomma(oRow.cells(1).innerText));
	}

	window.document.all.totalQty.value	= numFormatval(sumOfQty+'',2);
<?php } ?>
}
</script>
</head>
<body style="margin:8pt" onload="updateAmount()">
<table width="100%" cellpadding="0" class="main">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>DETAIL INITIAL STOCK</strong></td>
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
<input type='hidden' name='_code' value='<?php echo $column['it_code'] ?>'>
<table width="100%" class="table_l">
	<tr>
		<th width="20%">ITEM</th>
		<td colspan="2"><strong class="info"><font color="#446fbe" style="font-weight:bold">[<?php echo trim($column['it_code']) ?>]</font> <?php echo $column['it_model_no'] ?></strong></td>
	</tr>
	<tr>
		<th>DESC</th>
		<td><?php echo $column['it_desc'] ?></td>
	</tr>
	<tr>
	<th>LOCATION</th>
	<td>
<?php 
$wh = array($cboFilter[3]['purchasing'][ZKP_FUNCTION], count($cboFilter[3]['purchasing'][ZKP_FUNCTION]));
for($i=0; $i<$wh[1]; $i++) {
	$v = ($column["init_wh_location"]== $wh[0][$i][0])?' checked':'';
	echo "\t\t\t<input type=\"radio\" name=\"_location\" value=\"".$wh[0][$i][0]."\" id=\"".$wh[0][$i][1]."\"$v disabled><label for=\"".$wh[0][$i][1]."\"> ".$wh[0][$i][1]." </label>\n";
}
?>
	</td>
</tr>
<tr>
	<th>TYPE</th>
	<td>
		<input type="radio" name="_type_item" value="1" disabled<?php echo ($column["init_type"]==1) ? ' checked':''?>>NORMAL &nbsp;
		<input type="radio" name="_type_item" value="2" disabled<?php echo ($column["init_type"]==2) ? ' checked':''?>>DOOR TO DOOR
	</td>
</tr>
<tr>
	<th>QTY</th>
	<td><input type="text" name="_init_qty" class="fmtn" style="width:15%" value="<?php echo number_format($column["init_qty"],2)?>" readonly></td>
</tr>
</table>
</form>
    	</td>
    </tr>
    <tr height="10px">
    	<td></td>
    </tr>
<?php if($column["it_ed"] == 't') {?>
    <tr>
		<td valign="top"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
    	<td>
			<strong>E/D Information</strong>
			<table width="50%" class="table_l">
				<thead>
					<tr height="30px">
						<th>EXPIRED DATE</th>
						<th width="25%">QTY</th>
					</tr>
				</thead>
				<tbody id="EDPosition">
				<?php while($col = fetchRowAssoc($res_ed)) { ?>
					<tr id="<?php echo date('j-M-Y',strtotime($col["ined_expired_date"])) ?>">
						<td><input type="hidden" name="_ed_date[]" value="<?php echo date('j-M-Y',strtotime($col["ined_expired_date"])) ?>"><?php echo date('M-Y',strtotime($col["ined_expired_date"])) ?></td>
						<td align="right"><input type="hidden" name="_ed_qty[]" value="<?php echo $col["ined_qty"] ?>"><?php echo number_format($col["ined_qty"],2) ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<table width="50%" class="table_box">
				<tr>
					<th align="right">TOTAL</th>
					<th width="25%"><input type="text" name="totalQty" class="fmtn" style="width:100%" readonly></th>
				</tr>
			</table><br />
		</td>
	</tr>
<?php } ?>
</table>
<div align="right">
	<button name='btnClose' class='input_btn' style='width:100px;' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"> &nbsp; Close</button>
</div><br />
<!---------------------------------------- start print unconfirm ---------------------------------------->
<!--
<?php if($S->getValue("ma_authority") & 2048) { ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>UNCONFIRM INITIAL STOCK</strong></th>
    </tr>
</table><br />
<form name="frmUnconfirmed" method="post">
<input type="hidden" name="p_mode" value="unconfirmed">
<input type="hidden" name="_it_code" value="<?php echo $_code ?>">
<input type="hidden" name="_it_location" value="<?php echo $_location ?>">
<input type="hidden" name="_it_type" value="<?php echo $_type ?>">
<input type="hidden" name="_log_by_account" value="<?php echo $S->getValue('ma_account') ?>">
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Unconfirmed Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
			<table width="100%" class="table_box">
				<tr>
					<td width="10%">Name</td>
					<td width="2%">:</td>
					<td width="15%"><b><?php echo ucfirst($S->getValue('ma_account')) ?></b></td>
					<td width="10%">Remark</td>
					<td width="2%">:</td>
					<td width="25%"><input type="text" name="_remark" class="fmt" style="width:100%"></td>
					<td align="right">
						<button name='btnUnConfirm' class='input_btn' style='width:130px;'><img src="../../_images/icon/clean.gif" align="middle"> &nbsp; Unconfirm</button>
					</td>
				</tr>
			</table>
    	</td>
    </tr>
</table><br /><br />
</form>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUnconfirmed;

	window.document.frmUnconfirmed.btnUnConfirm.onclick = function() {
		var f = window.document.frmUnconfirmed;

		if(confirm("Are you sure to unconfirmed initial stock?")) {
			window.document.frmUnconfirmed.submit();
		}
	}
</script>
<?php } ?>
-->
<!---------------------------------------- end print unconfirm ---------------------------------------->
</body>
</html>