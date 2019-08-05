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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc 	   = "setup_initial_stock.php";
$isShowMessage = (isset($_GET['_show_message']) && $_GET['_show_message'] != '') ? $_GET['_show_message'] : false;

//INSERT ==============================================================================================================
if(ckperm(ZKP_INSERT,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {
	$_code = strtoupper($_POST['_cus_to']);
	$_dept = strtoupper($_POST['_dept']);

	//Item 
	foreach($_POST['_it_code'] as $val) $_it_code[] = $val;
	foreach($_POST['_it_qty'] as $val) $_it_qty[] = $val;
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty	= implode(',', $_it_qty);

	$result = executeSP(
		ZKP_SQL."_setupInitialApotikStock",
		"$\${$_code}$\$",
		"$\${$_dept}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_qty]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			goPage( HTTP_DIR . "$currentDept/$moduleDept/setup_initial_stock.php?_show_message=duplicate");
		}
	} 

	goPage( HTTP_DIR . "$currentDept/$moduleDept/setup_initial_stock.php?_show_message=true");
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
<script language="javascript" type="text/javascript">
function checkform(o) {
	if (window.rowPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		o.submit();
	}
}

function fillCustomer() {

	keyword = window.document.frmInsert._cus_to.value;

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'./p_list_cus_code.php?_check_code='+ keyword,
		'',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	win.focus();
}

//Open window for search item
var win;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;
	win = window.open('./p_list_item_for_setup.php','wSalesRpt',
		'scrollbars,width=580,height=620,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	win.focus();
}

//It Called by child window window's name is win
//Please see the p_list_item.php
function createItem(o) {

	var f2 = win.document.frmCreateItem;
	var oTR = window.document.createElement("TR");

	var oTD = new Array();
	var oHidden = new Array();

	//Check has same CODE
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.cells(0).innerText == f2.elements(0).value) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	for (var i=0; i<5; i++) { // 8 is number of TD
		oTD[i] = window.document.createElement("TD");
		oHidden[i] = window.document.createElement("INPUT");
		oHidden[i].type = "hidden";

		switch (i) {
			case 0: // CODE
				oTD[i].innerText = f2.elements(i).value;
				oHidden[i].name = "_it_code[]";
				break;

			case 1: // ITEM NO
				oTD[i].innerText = f2.elements(i).value;
				oHidden[i].name = "_it_model_no[]";
				break;

			case 2: // DESCRIPTION
				oTD[i].innerText = f2.elements(i).value;
				oHidden[i].name = "_it_desc[]";
				break;

			case 3: // QTY
				oTD[i].innerText = addcomma(f2.elements(i).value);
				oTD[i].align = "right";
				oHidden[i].name = "_it_qty[]";
				break;

			case 4: // DELETE
				oTD[i].align = "center";
				oTD[i].innerHTML = "<a href=\"javascript:deleteItem('" + oTD[0].innerText + "')\"><img src='../../_images/icon/delete.gif' sytle='width:12px'></a>";
				break;
		}

		oHidden[i].value = f2.elements(i).value;

		oTR.appendChild(oTD[i]);
		oTR.appendChild(oHidden[i]);
	}

	for (var i=0; i<4; i++) {
		f2.elements(i).value = '';
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
			count = count - 1;
		}
	}
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
		<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] SETUP INITIAL APOTIK STOCK</h4>
<?php
echo "<br /><span class=\"comment\">
*The quantity that you input will become initial STOCK only and added apotik inventory.<br/>To make current stock correctly, one item cannot duplicated input for same apotik.
</span><br /><br />";

if($isShowMessage == "true") {
	echo "<p align=\"right\" style=\"color:blue;\">\n";
	echo "* Initial JK sucessfully added in apotik inventory.";
	echo "</p>\n";
} elseif($isShowMessage == "duplicate")  {
echo "<marquee direction=\"left\" width=\"100%\" align=\"absmiddle\">Some of Item already exist in this initial JK. Please make sure again</marquee>";
}
?>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type='hidden' name='_dept' value='<?php echo $department ?>'>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="15%">CODE</th>
		<td width="10%"><input name="_cus_to" type="text" class="req" size="10" maxlength="7"></td>
		<th width="5%"><a href="javascript:fillCustomer()"><img src="../../_images/icon/search_mini.gif"></a></th>
		<td></td>
		<th width="15%">ATTN</th>
		<td width="43%"><input type="text" name="_cus_to_attn" class="fmt" style="width:100%" disabled></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="5"><input type="text" name="_cus_to_address" class="fmt"  style="width:100%" disabled></td>
	</tr>
</table><br />
<strong class="info">INITIAL STOCK LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="9%">CODE</th>
			<th width="17%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="9%">QTY</th>
			<th width="11%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save initial stock"> &nbsp; Save</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="setup_initial_stock.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel initial stock"> &nbsp; Cancel</button>
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