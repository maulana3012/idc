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
$left_loc 	   = "input_initial_stock.php";
$isShowMessage = (isset($_GET['_show_message']) && $_GET['_show_message'] != '') ? $_GET['_show_message'] : false;

//---------------------------------------------------------------------------------------------------- insert
if(ckperm(ZKP_INSERT,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_type 		= $_POST['_type'];
	$_location 	= $_POST['_location'];
	$_insert_by_account = $S->getValue("ma_account");

	//Item 
	foreach($_POST['_it_code'] as $val) $_it_code[]	= $val;
	foreach($_POST['_it_qty'] as $val)	$_it_qty[] 	= $val;
	foreach($_POST['_it_ed'] as $val)	$_it_ed[] 	= $val;

	//make pgsql ARRAY String for many item
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty	= implode(',', $_it_qty);
	$_it_ed		= '$$' . implode('$$,$$', $_it_ed) . '$$';
	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_qty'])) {
		foreach($_POST['_ed_qty'] as $val) {
			$_ed_qty[] 		 = $val;
		}
		$_ed_qty		= implode(',', $_ed_qty);
	} else {
		$_ed_qty	= '0';
	}

	if(isset($_POST['_ed_date'])) {
		foreach($_POST['_ed_date'] as $val) {
			$_ed_date[]		 = $val;
		}
		$_ed_date	= '$$' . implode('$$,$$', $_ed_date) . '$$';
	} else {
		$_ed_date	= '$$$$';
	}

	$result = executeSP(
		ZKP_SQL."_setupInitialStock",
		$_type,
		$_location,
		"$\${$_insert_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_qty]",
		"ARRAY[$_ed_date]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key value violates unique constraint")) {
			goPage( HTTP_DIR . "$currentDept/$moduleDept/input_initial_stock.php?_show_message=duplicate");
		}
	} else if(isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_initial_stock.php?_show_message=error");
	} else {
		goPage( HTTP_DIR . "$currentDept/$moduleDept/input_initial_stock.php?_show_message=true");
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
//Open window for search item
var win;
function fillItem(){
	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 640) / 2;
	win = window.open('./p_list_item_for_setup.php','wSalesRpt',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
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
		if (trim(oRow.cells(0).innerText) == trim(f2.elements(0).value)) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	for (var i=0; i<6; i++) {
		oTD[i] = window.document.createElement("TD");
		oHidden[i] = window.document.createElement("INPUT");
		oHidden[i].type = "hidden";

		switch (i) {
			case 0: // CODE
				if(f2.elements[4].value == 'true') {
					oTD[i].innerHTML	= "<a href=\"javascript:insertED('" + f2.elements[0].value + "','"+f2.elements[1].value+"')\"><b>"+f2.elements(i).value+"</b></a>";
				} else {
					oTD[i].innerText	= trim(f2.elements[0].value);
				}
				oHidden[i].name 	= "_it_code[]";
				oHidden[i].value	= f2.elements[0].value;
				break;

			case 1: // ITEM NO
				oTD[i].innerText	= f2.elements(i).value;
				oHidden[i].name		= "_it_model_no[]";
				oHidden[i].value	= f2.elements(i).value;
				break;

			case 2: // DESCRIPTION
				oTD[i].innerText	= f2.elements(i).value;
				oHidden[i].name		= "_it_desc[]";
				oHidden[i].value	= f2.elements(i).value;
				break;

			case 3: // QTY
				oTD[i].innerText	= addcomma(f2.elements(i).value);
				oTD[i].align		= "right";
				oHidden[i].name		= "_it_qty[]";
				oHidden[i].value	= f2.elements[3].value;
				break;

			case 4: // DELETE
				oTD[i].align		= "center";
				oTD[i].innerHTML	= "<a href=\"javascript:deleteItem('" + trim(oTD[0].innerText) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				break;

			case 5: // HAS ED
				oHidden[i].name		= "_it_ed[]";
				oHidden[i].value	= f2.elements[4].value;
				break;
		}
		if (i!=4) oTD[i].appendChild(oHidden[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);
	}
	for (var i=0; i<5; i++) {f2.elements[i].value = '';}
	window.rowPosition.appendChild(oTR);
}

//Delete Item wtd rows collection
function deleteItem(idx) {
	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == idx +'-'+ oRow.cells(1).innerText) {
			var n = window.EDPosition.removeChild(oRow);
			count = count - 1;
			i = i - 1;
		}
	}

	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
		}
	}
}

var wInputED;
function insertED(code, item) {
	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 200) / 2;

	wInputED = window.open(
		'./p_input_ed_initial.php?_code='+code+'&_item='+item,
		'wSearchED',
		'scrollbars,width=450,height=200,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

function createED() {
	var o	= window.document.frmInsert;
	var f2	= wInputED.document.frmCreateED;
	var d	= parseDate(f2.elements[2].value, 'prefer_euro_format');

	//Check has same CODE
	var count = EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)+'-'+formatDate(d, "NNN-yyyy")) {
			alert("Item code "+trim(f2.elements[0].value)+" with E/D "+formatDate(d, "NNN-yyyy")+" already exist!");
			return false;
		}
	}

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	for (var i=0; i<4; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText	= '['+trim(f2.elements[0].value)+'] '+f2.elements[1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // DATE
				oTD[i].innerText	= formatDate(d, "NNN-yyyy");
				oTD[i].align		= "center";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_date[]";
				oTextbox[i].value	= formatDate(d, "1-NNN-yyyy");
				break;

			case 2: // QTY
				oTD[i].innerText	= f2.elements[3].value;
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_qty[]";
				oTextbox[i].value	= removecomma(f2.elements[3].value);
				break;

			case 3: // DELETE
				oTD[i].innerHTML = "<a href=\"javascript:deleteED('" + trim(f2.elements[0].value)+'-'+formatDate(d, "NNN-yyyy")+ "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align = "center";
				break;
		}
		if (i!=3) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value)+'-'+formatDate(d, "NNN-yyyy");
		oTR.appendChild(oTD[i]);
	}
	window.EDPosition.appendChild(oTR);
}

function deleteED(code) {
	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == code) {
			var n = window.EDPosition.removeChild(oRow);
			count = count - 1;
		}
	}
}

function initPage() {

}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()">
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
		<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] SETUP INITIAL STOCK</h3>
<?php
if($isShowMessage != '') {
	echo "<hr>";

	if($isShowMessage == "true") {
		echo "<span style=\"font-family:Courier;color:#015396\">\n";
		echo "\t<img src=\"../../_images/icon/check.gif\"> Success add initial stock\n";
		echo "</span><br /><br />\n";
	} else if($isShowMessage == "duplicate") {
		echo "<span style=\"font-family:Courier;color:red\">";
		echo "\t<img src=\"../../_images/icon/alert.gif\"> Error while add initial stock. One or some item is duplicate!";
		echo "</span><br /><br />";
	} else if($isShowMessage == "error") {
		echo "<span style=\"font-family:Courier;color:red\">";
		echo "\t<img src=\"../../_images/icon/alert.gif\"> Error while add initial stock!";
		echo "</span><br /><br />";
	}
	echo "<div align=\"right\">";
	echo "\t<a href=\"input_initial_stock.php\" style=\"font-family:Courier;color:#015396\">Close this section <img src=\"../../_images/icon/close_mini.gif\"></a>";
	echo "</div><hr><br /><br />";
}
?>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<span class="bar">STOCK INFORMATION</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">TYPE</th>
		<td width="25%">
			<input type="radio" name="_type" value="1" id="vat" checked><label for="vat"> NORMAL &nbsp;</label>
			<input type="radio" name="_type" value="2" id="non"><label for="non"> DOOR TO DOOR</label>
		</td>
		<th width="15%">WAREHOUSE LOCATION</th>
		<td>
<?php 
$wh = array($cboFilter[3]['purchasing'][ZKP_FUNCTION], count($cboFilter[3]['purchasing'][ZKP_FUNCTION]));
for($i=0; $i<$wh[1]; $i++) {
	$v = ($i==0)?' checked':'';
	echo "\t\t\t<input type=\"radio\" name=\"_location\" value=\"".$wh[0][$i][0]."\" id=\"".$wh[0][$i][1]."\"$v><label for=\"".$wh[0][$i][1]."\"> ".$wh[0][$i][1]." </label>\n";
}
?>
		</td>
	</tr>
</table><br />
<span class="bar">ITEM LIST</span> <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l" cellspacing="1">
	<thead>
		<tr height="35px">
			<th width="9%">CODE</th>
			<th width="17%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">QTY</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
</table><br />
<span class="bar">E/D INFORMATION</span>
<table width="50%" class="table_l">
	<thead>
		<tr height="25px">
			<th width="50%">ITEM CODE</th>
			<th width="30%">EXPIRED DATE</th>
			<th width="15%">QTY</th>
			<th width="5%"></th>
		</tr>
	</thead>
	<tbody id="EDPosition">
	</tbody>
</table><br />
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save initial stock"> &nbsp; Save</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_initial_stock.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel input initial"> &nbsp; Cancel</button>
</p>
            <!--END: BODY-->
<script language="javascript" type="text/javascript">
window.document.all.btnSave.onclick = function() {
	var o = window.document.frmInsert;

	if (window.rowPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	//variable
	var countI		= window.rowPosition.rows.length;
	var countII		= window.EDPosition.rows.length;
	var e 			= window.document.frmInsert.elements;
	var numInput	= 5;
	var numInputII	= 3;
	var count_wh_loc = 0;
	if(window.document.frmInsert._location.length) {
		count_wh_loc = window.document.frmInsert._location.length;
	} else {
		count_wh_loc = 1;
	}
	var idx_code	= 3 + count_wh_loc;				/////
	var idx_item	= idx_code+1;
	var idx_qty		= idx_code+3;
	var idx_ed		= idx_code+4;
	var idx_codeII	= idx_code+(numInput*countI)+0;
	var idx_qtyII	= idx_code+(numInput*countI)+2;

	//checking E/D
	for (var i=0; i<countI; i++) {
		if(e(idx_ed+i*numInput).value=='true') {
			var istrue	= false;
			var code	= trim(e(idx_code+i*numInput).value);
			var item	= e(idx_item+i*numInput).value;
			var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

			if(countII<=0) { alert("Please complete data for Expired Date");return;}

			var temp_qty = 0;
			for (var j=0; j<countII; j++) {
				if(e(idx_codeII+j*numInputII).value==code) {
					if(parseFloat(removecomma(e(idx_qtyII+j*numInputII).value))=='') {
						var value = 0;
					} else {
						var value = parseFloat(removecomma(e(idx_qtyII+j*numInputII).value));
					}
					temp_qty = temp_qty + value;
				}
			}

			if(temp_qty != qty) {
				alert(
					"Item qty don't match with E/D qty.\nCheck the list for:\n\n" +
					"Code : ["+ trim(code) + "] "+  item + "\n" +
					"Current input qty           : "+addcomma(qty)+"\n" +
					"Current inputed E/D qty : "+addcomma(temp_qty));
				return;
			}
 		}
	}

	if (verify(o)) {
		if(confirm("Are you sure to save this initial stock?")) {
			o.submit();
		}
	}
}
</script>
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