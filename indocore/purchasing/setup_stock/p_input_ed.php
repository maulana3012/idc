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
ckperm(ZKP_INSERT, "javascript:window.close();");

//Check PARAMETER
if(!isset($_GET['_code']) && $_GET['_code'] != '')
	die("<script language=\"javascript1.2\">window.close();</script>");

$strGet		= "";
$_code		= trim($_GET['_code']);
$_item		= trim($_GET['_item']);
$_vat		= isset($_GET['_vat']) ? trim($_GET['_vat']) : "";
$_non		= isset($_GET['_non']) ? trim($_GET['_non']) : "";
$_max		= isset($_GET['_max']) ? trim($_GET['_max']) : "";
$_loc		= isset($_GET['_loc']) ? $_GET['_loc'] : 2;
$_type		= isset($_GET['_type']);
$_bor_idx	= isset($_GET['_bor_idx']) ? trim($_GET['_bor_idx']) : "";

if($_GET['_act'] == 'move_location' || $_GET['_act'] == 'move_type_rev') {
	$_type		= $_GET['_type'];
} else if($_GET['_act'] == 'move_type') {
	if($_GET['_type'] == 1)			$_type = 2;
	else if($_GET['_type'] == 2)	$_type = 1;
}

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT e_expired_date, e_wh_location, e_type, e_qty, ".ZKP_SQL."_getRemainMonth(e_expired_date) AS remain_month FROM ".ZKP_SQL."_tb_expired_stock");
$sqlQuery->whereCaluse = "e_qty>0 AND it_code='$_code' AND e_wh_location=$_loc AND e_type=$_type";
$strGet = "&_code=$_code&_loc=$_loc&_type=$_type";
$sqlQuery->setOrderBy("e_expired_date");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");
$numRow = numQueryRows($result);
?>
<html>
<head>
<title>SET EXPIRED DATE</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var rows = new Array();\n";
$i = 0;
while ($rows =& fetchRow($result, 0)) {
	printf("rows['%s'] = [%s,%s];\n",
		addslashes($rows[0]),	//expired_date
		$rows[3],				//qty
		$rows[4]				//remain month
	); 
}
?>

function setValue() {
	var f = window.document.frmInsert;
	var selected_date	= f._rows_date.value;
	if(selected_date!=''){
		f._max_qty.value		= addcomma(rows[selected_date][0]);
		f._remain_month.value	= rows[selected_date][1];
	} else {
		f._max_qty.value		= '';
	}
}

function createNewED() {
	var f = window.document.frmInsert;

	if(f._rows_date.value == '') {
		alert("Please choose E/D first");
		return;
	} else if(f._qty.value == '' || f._qty.value== 0) {
		alert("Please input qty");
		return;
	} else if(parseFloat(removecomma(f._qty.value)) > parseFloat(removecomma(f._max_qty.value))) {
		alert("You can use qty more than available stock in this E/D");
		return;
	}
	window.opener.createED();
	window.document.frmInsert._qty.value='';
	window.document.frmInsert._qty.focus();
}

function initPage() {
	var f	= window.document.frmSearch;
	var nama = window.name;

	if(window.name == 'move_type') {
		if(f._type.value == '1') {
			window.document.frmSearch._type_item[0].checked = true;
		} else if(f._type.value == '2') {
			window.document.frmSearch._type_item[1].checked = true;
		}
	}
}
</script>
</head>
<body style="margin:8pt" onLoad="initPage()">
<table width="100%" cellpadding="0">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting.gif">&nbsp;&nbsp;<strong>ITEM DESCRIPTION</strong></td>
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
<input type='hidden' name='_code' value='<?php echo $_code ?>'>
<input type='hidden' name='_item' value='<?php echo $_item ?>'>
<input type='hidden' name='_loc' value='<?php echo $_loc ?>'>
<input type='hidden' name='_type' value='<?php echo $_type ?>'>
<table width="100%" class="table_l">
	<tr>
		<th width="25%">ITEM</th>
		<td><strong class="info"><font color="#446fbe" style="font-weight:bold">[<?php echo $_code ?>]</font> <?php echo $_item ?></strong></td>
	</tr>
	<tr>
		<th>LOCATION</th>
		<td>
		<?php 
		$wh = array($cboFilter[3]['purchasing'][ZKP_FUNCTION], count($cboFilter[3]['purchasing'][ZKP_FUNCTION]));
		for($i=0; $i<$wh[1]; $i++) {
			$v = (intval($_loc)==intval($wh[0][$i][0]))?' checked':'';
			echo "\t\t\t<input type=\"radio\" name=\"_location\" value=\"".$wh[0][$i][0]."\" id=\"".$wh[0][$i][1]."\" disabled $v><label for=\"".$wh[0][$i][1]."\"> ".$wh[0][$i][1]." </label> &nbsp; &nbsp;";
		}
		?>
		</td>
	</tr>
	<tr>
		<th>TYPE</th>
		<td>
			<input type="radio" name="_type_item" value="1" id="1" disabled<?php echo ($_type==1)?' checked':'' ?>><label for="1">VAT</label>
			<input type="radio" name="_type_item" value="2" id="2" disabled<?php echo ($_type==2)?' checked':'' ?>><label for="2">NON</label>
		</td>
		<td></td>
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
<form name="frmInsert" method="GET">
<input type='hidden' name='_code' value='<?php echo $_code ?>'>
<input type='hidden' name='_item' value='<?php echo $_item ?>'>
<input type='hidden' name='_loc' value='<?php echo $_loc ?>'>
<input type='hidden' name='_type' value='<?php echo $_type ?>'>
<strong>Select E/D Stock</strong>
<table width="100%" class="table_l">
	<tr>
		<th width="25%">E/D</th>
		<td width="25%">
			<select name="_rows_date" onChange="setValue()">
				<option value=""></option>
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
	echo "\t\t\t\t<option value=\"".$column["e_expired_date"]."\">".date('M-Y', strtotime($column["e_expired_date"]))."</option>\n";
}
?>
			</select>
		</td>
		<th width="25%">M/S</th>
		<td><input type="text" name="_remain_month" size="3" class="fmtn" readonly> month/s</td>
	</tr>
	<tr>
		<th>STOCK</th>
		<td><input type="text" name="_max_qty" style="width:60%" class="fmtn" readonly></td>
		<th>MOVE</th>
		<td><input type="text" name="_qty" class="reqn" style="width:60%" value="0" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewED()"></td>
	</tr>
</table>
<input type="hidden" name="_bor_idx" value="<?php echo $_bor_idx ?>">
</form>
<div align="right">
	<button name='btnAdd' class='input_sky' style='width:50px;height:25px' onclick='createNewED()'><img src="../../_images/icon/add.gif" width="15px" align="middle" alt="Add"></button>&nbsp;
	<button name='btnClose' class='input_sky' style='width:50px;height:25px' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"></button>
</div>
    	</td>
    </tr>
</table><br /><br />
</body>
</html>