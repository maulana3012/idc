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
ckperm(ZKP_INSERT, HTTP_DIR ."$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_group_price_policy.php";

//========================================================================================== INSERT PROCESS
if (ckperm(ZKP_INSERT, HTTP_DIR ."$currentDept/$moduleDept/index.php", 'insert')) {

	$_cug_code	= strtoupper($_POST['_cug_code']);
	$_basic_disc_pct = $_POST['_basic_disc_pct'];
	$_disc_pct	= $_POST['_disc_pct'];
	$_desc		= $_POST['_desc'];
	$_is_valid	= ($_POST['_is_valid'] == '1') ? "TRUE" : "FALSE";
	$_is_apply_all	= ($_POST['_is_apply_all'] == '1') ? "TRUE" : "FALSE";
	$_date_from	= $_POST['_date_from'];
	$_date_to	= $_POST['_date_to'];
	$_remark	= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val) $_it_code[] = $val;

	//make pgsql ARRAY String for many item
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';

	$result = executeSP(
		ZKP_SQL."_addGroupPrice",
		"$\${$_cug_code}$\$",
		"$\${$_desc}$\$",
		$_basic_disc_pct,
		$_disc_pct,
		$_is_valid,
		$_is_apply_all,
		"$\${$_date_from}$\$",
		"$\${$_date_to}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"$\$".$S->getValue("ma_account")."$\$"); //created

	if (isZKError($result)) {
		if(preg_match("/_([0-9]+)_ITEM_([\w]+)/",$result->getMessage(), $match)) {
			$o = new ZKError("DUPLICATE_PERIOD_AND_ADD_DISCOUNT",
						 "DUPLICATE_PERIOD_AND_ADD_DISCOUNT",
						 "Duplicated period and additional discount found. GROUP POLICY #{$match[1]}, ITEM CODE: {$match[2]}<br>Please check the group policy of this item after click [CONFIRM]");
			$M->goErrorPage($o, HTTP_DIR ."$currentDept/$moduleDept/detail_group_policy.php?_code=$match[1]");
		}
		$M->goErrorPage($result, HTTP_DIR ."$currentDept/$moduleDept/list_group_policy.php");
	} else {
		$M->goPage(HTTP_DIR ."$currentDept/$moduleDept/list_group_policy.php");
	}
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
<script language="javascript1.2" type="text/javascript">
function checkform(o) {
	var d1 = parseDate(o._date_from.value, 'prefer_euro_format');
	var d2 = parseDate(o._date_to.value, 'prefer_euro_format');
	
	if(d1 == null || d2 == null) {
		alert("Please input correct date");
		o._date_from.value = '';
		o._date_to.value = '';
		o._date_from.focus();
		return;
	} else if (d1.getTime() > d2.getTime()) {
		alert("TO date is more earlier than FROM date");
		o._date_from.value = '';
		o._date_to.value = '';
		o._date_from.focus();
		return;
	}

	if (o._is_apply_all[1].checked && window.rowPosition.rows.length == 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		o.submit();
	}
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var f = window.document.frmInsert;
	if (f._basic_disc_pct.value == '' || f._disc_pct.value == '') {
		alert("please choose GROUP and specify ADDITIONAL DISCOUNT first");
		return;
	}

	if (f._is_apply_all[0].checked) {
		alert("You already choose [ALL ITEM]");
		return;
	}

	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;
	wSearchItem = window.open('./p_list_item_for_apotik_policy.php','wSearchItem',
		'scrollbars,width=580,height=620,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
//Please see the p_list_item.php
function createItem(arrItem) {

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oHidden = new Array();
	
	//Check has same CODE
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (trim(oRow.cells[0].innerText) == trim(arrItem[0])) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	//Price
	var basic_disc = parseFloat(window.document.frmInsert._basic_disc_pct.value);
	var additional_disc = parseFloat(window.document.frmInsert._disc_pct.value);
	var user_price = Math.round(parseFloat(arrItem[3])/10)*10;
	var apotik_price = Math.round(((user_price - user_price * basic_disc/100)/1.1)/10)*10;
	var disc_price = Math.round(((user_price - user_price * (basic_disc + additional_disc)/100)/1.1)/10)*10;

	//If you add more cell
	// 1. increase tthe count as number of td
	// 2. add Case
	// the Cell order match with p_list_item.php field.
	for (var i=0; i<7; i++) { //5 is number of TD
		oTD[i] = window.document.createElement("TD");
		oHidden[i] = window.document.createElement("INPUT");
		oHidden[i].type = "hidden";

		switch (i) {
			case 0: // CODE
				oTD[i].innerText = arrItem[0];
				oHidden[i].name = "_it_code[]";
				oHidden[i].value = arrItem[i];
				break;

			case 1: // ITEM NO
				oTD[i].innerText = cut_string(arrItem[1],10);
				break;

			case 2: // DESCRIPTION
				oTD[i].innerText = cut_string(arrItem[2],40);
				break;

			case 3: // USER PRICE
				oTD[i].innerText = numFormatval(user_price + '', 0);
				oTD[i].align = "right";
				break;

			case 4: // APOTIK PRICE
				oTD[i].innerText = numFormatval(apotik_price + '', 0);
				oTD[i].align = "right";
				break;

			case 5: // DISC PRICE
				oTD[i].innerText = numFormatval(disc_price + '', 0);
				oTD[i].align = "right";
				break;

			case 6: // DELETE
				oTD[i].innerHTML = "<a href=\"javascript:deleteItem('" + arrItem[0] + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align = "center";
				break;
		}

		oTR.appendChild(oTD[i]);
		oTR.appendChild(oHidden[i]);
	}

	window.rowPosition.appendChild(oTR);
}

//Delete Item wtd rows collection
function deleteItem(idx) {
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.cells(0).innerText == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1; //decrease loop - 1
		}
	}
}

function deleteAll() {
	window.rowPosition.removeNode();
	window.location.reload();
}
</script>
</head>
<body topmargin="0" leftmargin="0">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#9CBECC">
  <tr>
    <td>
			<?php require_once APP_DIR . "_include/tpl_header.php"?>
    </td>
  </tr>
  <tr>
    <td style="padding:5 10 0 10" valign="bottom">
			<?php require_once APP_DIR . "_include/tpl_topMenu.php";?>
    </td>
  </tr>
  <tr>
    <td style="padding:0 3 3 3">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
			<?php require_once "_left_menu.php";?>
			<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<h4>NEW GROUP PRICE POLICY</h4>
<form name='frmInsert' method='POST'>
<input type="hidden" name="p_mode" value="insert">
<table width="100%" class="table_box">
	<tr>
		<th width="12%">GROUP CODE</th>
		<td>
<?php
	$sql = "SELECT cug_code, cug_name, cug_basic_disc_pct FROM ".ZKP_SQL."_tb_customer_group ORDER BY cug_name";
	isZKError($result = & query($sql)) ? $M->printMessage($result):0;

	if(numQueryRows($result) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the $arg first. you can find the [new ". ucfirst($arg) ."] under the BASIC DATA menu");
		$M->printMessage($result);
	} else {
		print "<select name=\"_cug_code\" class=\"req\">\n";
		print "\t<option value=\"\">==SELECT==</option>\n";
	
		while ($columns = fetchRow($result)) {
			print "\t<option value=\"".$columns[0]."\">".$columns[1]."</option>\n";
		}
		print "</select>\n";
	}
?>
		</td>
		<th>BASIC DISC%</th>
		<td>
<script language="javascript1.2" type="text/javascript">
	var cus_disc = new Array();
<?php
pg_result_seek($result, 0);
while($col =& fetchRow($result))
	echo "\tcus_disc['$col[0]'] = $col[2];\n";
?>

	window.document.frmInsert._cug_code.onchange = function() {
		window.document.frmInsert._basic_disc_pct.value = cus_disc[this.value];
	}
</script>
		<input type="text" name="_basic_disc_pct" value="" size="5" class="reqn" maxlength="4" readonly>
		</td>
	</tr>
	<tr>
		<th width="12%">DESC</th>
		<td><input name="_desc" class="req" style="width:90%"></td>
		<th>VALID</th>
		<td><input type="radio" name='_is_valid' value='1' checked>YES, <input type="radio" name='_is_valid' value='0'>No</td>
	</tr>
	<tr>
		<th width="12%">PERIOD</th>
		<td>FROM: <input type="text" name="_date_from" class="reqd" size="8"> 
			TO: <input type="text" name="_date_to" class="reqd" size="8"> &nbsp;
			ADDITIONAL DISCOUNT: <input type="text" class="reqn" maxlength="4" name="_disc_pct" size="3">%</td>
		<th>ALL ITEM</th>
		<td><input type="radio" name='_is_apply_all' value='1' onClick="deleteAll()" checked>YES, <input type="radio" name='_is_apply_all' value='0'>No</td>
	</tr>
</table><br>
<strong>APPLIED ITEM</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l">
	<thead>
		<tr>
			<th width="8%">CODE</th>
			<th width="12%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">USER PRICE</th>
			<th width="10%">A/PRICE <br >W/O VAT</th>
			<th width="10%">DISC PRICE<br >W/O VAT</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
</table><br>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">REMARK</th>
		<td><textarea name="_remark" cols="80" rows="5"></textarea></td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save group price policy"> &nbsp; Save </button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_group_price_policy.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel group price policy"> &nbsp; Cancel </button>
</p>
            <!--END: BODY-->
          </td>
        </tr>
      </table>
      </td>
  </tr>
  <tr>
    <td style="padding:5 10 5 10" bgcolor="#FFFFFF">
			<?php require_once APP_DIR . "_include/tpl_footer.php"?>
    </td>
  </tr>
</table>
</body>
</html>