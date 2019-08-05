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

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT d_expired_date, d_qty, ".ZKP_SQL."_getRemainMonth(d_expired_date) AS remain_month FROM ".ZKP_SQL."_tb_expired_demo");
$sqlQuery->whereCaluse = "d_qty!=0 AND it_code='$_code'";
$strGet = "&_code=$_code";
$sqlQuery->setOrderBy("d_expired_date");
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
		$rows[1],	//qty
		$rows[2]	//remain month
	); 
}
?>

function setValue() {
	var f = window.document.frmInsert;
	var selected_date	= f._rows_date.value;
	if(selected_date!=''){
		f._max_qty.value	= addcomma(rows[selected_date][0]);
		f._remain_month.value	= rows[selected_date][1];
	} else {
		f._max_qty.value	= '';
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
</script>
</head>
<body style="margin:8pt">
<table width="100%" cellpadding="0" class="main">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting_mini.gif">&nbsp;&nbsp;<strong>ITEM DESCRIPTION</strong></td>
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
<table width="100%" class="table_l">
	<tr>
		<th>ITEM</th>
		<td colspan="2"><strong class="info"><font color="#446fbe" style="font-weight:bold">[<?php echo $_code ?>]</font> <?php echo $_item ?></strong></td>
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
<strong>Select E/D Stock</strong>
<table width="100%" class="table_l">
	<tr>
		<th width="25%">E/D</th>
		<td width="25%">
			<select name="_rows_date" onchange="setValue()">
				<option value=""></option>
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
	echo "\t\t\t\t<option value=\"".$column["d_expired_date"]."\">".date('M-Y', strtotime($column["d_expired_date"]))."</option>\n";
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
		<td colspan="2"></td>
	</tr>
	<tr>
		<th>USE</th>
		<td><input type="text" name="_qty" class="reqn" style="width:60%" value="0" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewED()"></td>
		<td colspan="2" align="right">
			<button name="btnAdd" class="fmt" style="width:60px;height:25px" onclick="createNewED()">ADD</button>
			<button name="btnClose" class="fmt" style="width:70px;height:25px" onclick="window.close()">Close <img src="../../_images/icon/close.gif"></button>
		</td>
	</tr>
</table>
</form>
    	</td>
    </tr>
</table>
</body>
</html>