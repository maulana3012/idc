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
$left_loc		= "input_sales_report.php";
$isShowMessage 	= (isset($_GET['_show_message']) && $_GET['_show_message'] != '') ? $_GET['_show_message'] : false;

//---------------------------------------------------------------------------------------------------- insert
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_code = strtoupper($_POST['_cus_to']);
	$_dept = strtoupper($_POST['_dept']);
	$_cus_to_responsible_by = $_POST['_cus_to_responsible_by'];

	//ITEM LIST
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_qty'] as $val)			$_it_qty[]			= $val;
	foreach($_POST['_it_sales_date'] as $val)	$_it_sales_date[]	= $val;
	foreach($_POST['_it_pay_price'] as $val)	$_it_pay_price[]	= $val;
	foreach($_POST['_it_faktur_no'] as $val)	$_it_faktur_no[]	= $val;
	foreach($_POST['_it_lop_no'] as $val)		$_it_lop_no[]		= $val;
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty		= implode(',', $_it_qty);
	$_it_sales_date	= "DATE '" . implode("', DATE '", $_it_sales_date) . "'";
	$_it_pay_price	= implode(',', $_it_pay_price);
	$_it_faktur_no	= '$$' . implode('$$,$$', $_it_faktur_no) . '$$';
	$_it_lop_no		= '$$' . implode('$$,$$', $_it_lop_no) . '$$';

	$result = executeSP(
		ZKP_SQL."_addSalesData",
		"$\${$_code}$\$",
		"$\${$_dept}$\$",
		$_cus_to_responsible_by,
		"ARRAY[$_it_code]",
		"ARRAY[$_it_sales_date]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_it_pay_price]",
		"ARRAY[$_it_faktur_no]",
		"ARRAY[$_it_lop_no]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		goPage(HTTP_DIR . "$currentDept/$moduleDept/input_sales_report.php?_show_message=true");
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
<script language="javascript" type="text/javascript">
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
		addOption(document.frmInsert._cus_to_responsible_by,mkt[i][1], mkt[i][0]);
}

function checkform(o) {
	if (window.rowPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if(o._cus_to_responsible_by.value == 0) {
		alert("Responsibly by must be entered");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save sales logs?")) {
			o.submit();
		}		
	}
}

function fillCustomer() {
	keyword = window.document.frmInsert._cus_to.value;
	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'./p_list_cus_code.php?_check_code='+ keyword,
		'input_sales',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	win.focus();
}

//Open window for search item
var win;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;
	win = window.open('./p_list_item_for_salesrpt.php','wSalesRpt',
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

	for (var i=0; i<8; i++) {
		oTD[i] = window.document.createElement("TD");
		oHidden[i] = window.document.createElement("INPUT");
		oHidden[i].type = "hidden";

		switch (i) {
			case 0: // CODE
				oTD[i].innerText	= f2.elements(i).value;
				oHidden[i].name		= "_it_code[]";
				break;

			case 1: // ITEM NO
				oTD[i].innerText	= f2.elements(i).value;
				oHidden[i].name		= "_it_model_no[]";
				break;

			case 2: // SALES DATE
				oTD[i].innerText	= f2.elements(i).value;
				oTD[i].align		= "center";
				oHidden[i].name		= "_it_sales_date[]";
				break;

			case 3: // QTY
				oTD[i].innerText	= addcomma(f2.elements(i).value);
				oTD[i].align		= "right";
				oHidden[i].name		= "_it_qty[]";
				break;

			case 4: // PAY PRICE
				oTD[i].innerText	= addcomma(f2.elements(i).value);
				oTD[i].align		= "right";
				oHidden[i].name		= "_it_pay_price[]";
				break;

			case 5: // FAKTUR / BILL NO
				oTD[i].innerText	= f2.elements(i).value;
				oTD[i].align		= "center";
				oHidden[i].name		= "_it_faktur_no[]";
				break;

			case 6: // LOP NO
				oTD[i].innerText	= f2.elements(i).value;
				oTD[i].align		= "center";
				oHidden[i].name		= "_it_lop_no[]";
				break;

			case 7: // DELETE
				oTD[i].align		= "center";
				oTD[i].innerHTML	= "<a href=\"javascript:deleteItem('" + oTD[0].innerText + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				break;
		}
		if(i!= 7) oHidden[i].value = f2.elements(i).value;
		oTR.appendChild(oTD[i]);
		oTR.appendChild(oHidden[i]);
	}

	for (var i=0; i<6; i++) {f2.elements(i).value = '';}
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
<body topmargin="0" leftmargin="0" onLoad="initOption();">
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] INPUT SALES LOGS<br />
</strong>
<hr><br />
<?php
if($isShowMessage != '') {
	echo "<hr>";

	if($isShowMessage == "true") {
		echo "<span style=\"font-family:Courier;color:#015396\">\n";
		echo "\t<img src=\"../../_images/icon/check.gif\"> Sales data sucessfully added in inventory\n";
		echo "</span><br /><br />\n";
	}
	echo "<div align=\"right\">";
	echo "\t<a href=\"input_sales_report.php\" style=\"font-family:Courier;color:#015396\">Close this section <img src=\"../../_images/icon/close_mini.gif\"></a>";
	echo "</div><hr><br /><br />";
}
?>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<strong>SALES CONDITION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="15%">CODE</th>
		<td width="10%"><input name="_cus_to" type="text" class="req" size="10" maxlength="7"></td>
		<th width="3%"><a href="javascript:fillCustomer()"><img src="../../_images/icon/search_mini.gif" width="15px" align="middle" alt="Search customer"></a></th>
		<td width="5%"></td>
		<th width="12%">ATTN</th>
		<td><input type="text" name="_cus_to_attn" class="fmt" style="width:100%" disabled></td>
	</tr>
	<tr>
		<th>RESPONSIBLE BY</th>
		<td colspan="3">
	        <select name="_cus_to_responsible_by" id="_cus_to_responsible_by" class="fmt">
                <option value="0">==SELECT==</option>
            </select>
		</td>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_to_address" class="fmt"  style="width:100%" disabled></td>
	</tr>
</table><br />
<strong>SOLD ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l">
	<thead>
		<tr>
			<th width="9%">CODE</th>
			<th>ITEM NO</th>
			<th width="15%">SALES<br>DATE</th>
			<th width="5%">QTY</th>
			<th width="12%">@PRICE<br>IN REPORT</th>
			<th width="18%">FAKT/BILL<br />No.</th>
			<th width="15%">LOP No.</th>
			<th width="3%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save sales"> &nbsp; Save sales</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_sales_report.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel sales"> &nbsp; Cancel sales</button>
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