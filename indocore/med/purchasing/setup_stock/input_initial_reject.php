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
$left_loc 	   = "input_initial_reject.php";
$isShowMessage = (isset($_GET['_show_message']) && $_GET['_show_message'] != '') ? $_GET['_show_message'] : false;

//---------------------------------------------------------------------------------------------------- insert
if(ckperm(ZKP_INSERT,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_log_by_account = $S->getValue("ma_account");

	//Item 
	foreach($_POST['_it_code'] as $val)				$_it_code[]				= $val;
	foreach($_POST['_it_serial_no'] as $val)		$_it_serial_no[] 		= $val;
	foreach($_POST['_it_expired_warranty'] as $val)	$_it_expired_warranty[]	= $val;
	foreach($_POST['_it_desc'] as $val)				$_it_desc[]				= $val;
	foreach($_POST['_it_type'] as $val)				$_it_type[]				= $val;
	foreach($_POST['_it_location'] as $val)			$_it_location[]			= $val;

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_serial_no	= '$$' . implode('$$,$$', $_it_serial_no) . '$$';
	$_it_expired_warranty	= '$$' . implode('$$,$$', $_it_expired_warranty) . '$$';
	$_it_desc		= '$$' . implode('$$,$$', $_it_desc) . '$$';
	$_it_type		= implode(',', $_it_type);
	$_it_location	= implode(',', $_it_location);

	$result = executeSP(
		ZKP_SQL."_setupRejectStock",
		"$\${$_log_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_serial_no]",
		"ARRAY[$_it_expired_warranty]",
		"ARRAY[$_it_desc]",
		"ARRAY[$_it_type]",
		"ARRAY[$_it_location]"
	);

	if(isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_initial_reject.php?_show_message=error");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/input_initial_reject.php?_show_message=true");
}

//--------------------------------------------------------------------------------------------- DEFAULT PROCESS
$stock_sql	= "SELECT it_code, it_model_no, ".ZKP_SQL."_getStock(it_code,1,1),".ZKP_SQL."_getStock(it_code,1,2),".ZKP_SQL."_getStock(it_code,2,1),".ZKP_SQL."_getStock(it_code,2,2) 
			   FROM ".ZKP_SQL."_tb_item where it_ed is false AND it_status = 0 ORDER BY it_code";
$stock_res	= query($stock_sql);
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkForm() {
	var f = window.document.frmInsert;

	if (window.rowPosition.rows.length <= 0) {
		alert("You need to fill at least 1 item");
		return;
	}

	if (verify(f)) {
		if(confirm("Are you sure to move this stock to reject item?")) {
			f.submit();
		}
	}
}

//Open window for search item
var wSearchItem;
function fillItem() {
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;

	wSearchItem = window.open("./p_list_reject_stock.php",'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem() {

	var f	= window.document.frmInsert;
	var f2	= wSearchItem.document.frmCreateItem;
	var oTR	= window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == f2.elements[0].value+'-'+f2.elements[2].value) {
			alert("Same Item with same serial number already exist!");
			return;
		}
	}

	for (var i=0; i<9; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // _it_code
				oTD[i].innerText 		= trim(f2.elements[0].value);
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_code[]";
				oTextbox[i].value		= trim(f2.elements[0].value);
				break;

			case 1: // _it_model_no
				oTD[i].innerText 		= f2.elements[1].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_model_no[]";
				oTextbox[i].value		= f2.elements[1].value;
				break;

			case 2: // _it_serial_no
				oTD[i].innerText		= f2.elements[2].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_serial_no[]";
				oTextbox[i].value		= f2.elements[2].value;
				break;

			case 3: // _it_expired_warranty
				oTD[i].innerText		= f2.elements[3].value;
				oTD[i].align			= 'center';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_expired_warranty[]";
				oTextbox[i].value		= f2.elements[3].value;
				break;

			case 4: // _it_qty
				oTD[i].innerText		= '1';
				oTD[i].align			= 'right';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_qty[]";
				oTextbox[i].value		= '1';
				break;

			case 5: // _it_desc
				oTD[i].innerText		= f2.elements[5].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_desc[]";
				oTextbox[i].value		= f2.elements[5].value;
				break;

			case 6: // _it_type
				if(f2.elements[6].value == 1) {
					var type			= 1;
					oTD[i].innerText	= 'VAT';
					oTD[i].align		= 'center';
				} else if(f2.elements[6].value == 2) {
					var type			= 2;
					oTD[i].innerText	= 'NON';
					oTD[i].align		= 'center';
				}

				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_type[]";
				oTextbox[i].value		= type;
				break;

			case 7: // _it_location
				var wh_name = '';
				if (f._function.value == 'IDC') {
					if(f2.elements[9].value == 1) {
						wh_name = 'IDC';
					} else if(f2.elements[9].value == 2) {
						wh_name = 'DNR';
					}
				} else if (f._function.value == 'MED') {
					if(f2.elements[9].value == 1) {
						wh_name = 'MED';
					} 
				}
				var loc					= f2.elements[2].value;
				oTD[i].innerText		= wh_name;
				oTD[i].align			= 'center';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_location[]";
				oTextbox[i].value		= loc;
				break;

			case 8: // [del]
				oTD[i].align			= "center";
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + f2.elements[0].value+'-'+f2.elements[2].value + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				break;

		}
		if (i<8) oTD[i].appendChild(oTextbox[i]);
		oTR.id = f2.elements[0].value+'-'+f2.elements[2].value;
		oTR.appendChild(oTD[i]);
	}
	window.rowPosition.appendChild(oTR);
	for (var i=0; i<12; i++) {f2.elements[i].value = '';}
}

function deleteItem(code) {
	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == code) {
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
		<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ADD REJECT STOCK</h3>
<?php
if($isShowMessage != '') {
	echo "<hr>";

	if($isShowMessage == "true") {
		echo "<span style=\"font-family:Courier;color:#015396\">\n";
		echo "\t<img src=\"../../_images/icon/check.gif\"> Success add reject stock\n";
		echo "</span><br /><br />\n";
	} else if($isShowMessage == "error") {
		echo "<span style=\"font-family:Courier;color:red\">";
		echo "\t<img src=\"../../_images/icon/alert.gif\"> Error while add reject stock!";
		echo "</span><br /><br />";
	}
	echo "<div align=\"right\">";
	echo "\t<a href=\"input_initial_reject.php\" style=\"font-family:Courier;color:#015396\">Close this section <img src=\"../../_images/icon/close_mini.gif\"></a>";
	echo "</div><hr><br /><br />";
}
?>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type='hidden' name='_function' value='<?php echo ZKP_SQL ?>'>
<span class="bar_bl">ITEM LIST</span> <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l" cellspacing="1">
	<thead>
		<tr height="25px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th width="15%">SERIAL NO</th>
			<th width="8%">EXP.<BR />WARRANTY</th>
			<th width="5%">QTY</th>
			<th>DESCRIPTION</th>
			<th width="5%">TYPE</th>
			<th width="5%">LOC</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkForm()'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save move to reject"> &nbsp; Save</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_initial_reject.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel move stock to reject"> &nbsp; Cancel</button>
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