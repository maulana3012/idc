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
$left_loc 	   = "input_move_location.php";
$isShowMessage = (isset($_GET['_show_message']) && $_GET['_show_message'] != '') ? $_GET['_show_message'] : false;

//---------------------------------------------------------------------------------------------------- insert
if(ckperm(ZKP_DELETE,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_log_by_account = $S->getValue("ma_account");

	//Item 
	foreach($_POST['_it_code'] as $val)				$_it_code[]			= $val;
	foreach($_POST['_it_type'] as $val)				$_it_type[] 		= $val;
	foreach($_POST['_it_location_from'] as $val)	$_it_location_from[]= $val;
	foreach($_POST['_it_location_to'] as $val)		$_it_location_to[]	= $val;
	foreach($_POST['_it_qty'] as $val)				$_it_qty[]			= $val;
	foreach($_POST['_it_remark'] as $val)			$_it_remark[]		= $val;

	//make pgsql ARRAY String for many item
	$_it_code			= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_type			= implode(',', $_it_type);
	$_it_location_from	= implode(',', $_it_location_from);
	$_it_location_to	= implode(',', $_it_location_to);
	$_it_qty			= implode(',', $_it_qty);
	$_it_remark			= '$$' . implode('$$,$$', $_it_remark) . '$$';

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[] = $val;
		}
		$_ed_it_code = '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {$_ed_it_code = '$$$$';}

	if(isset($_POST['_ed_it_location'])) {
		foreach($_POST['_ed_it_location'] as $val) {
			$_ed_it_location[] = $val;
		}
		$_ed_it_location = implode(',', $_ed_it_location);
	} else {$_ed_it_location = '0';}

	if(isset($_POST['_ed_it_type'])) {
		foreach($_POST['_ed_it_type'] as $val) {
			$_ed_it_type[] = $val;
		}
		$_ed_it_type = implode(',', $_ed_it_type);
	} else {$_ed_it_type = '0';}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[] = $val;
		}
		$_ed_it_date = '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {$_ed_it_date = '$$$$';}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = number_format($val,2);
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {$_ed_it_qty	= '0.00';}

	$result = executeSP(
		ZKP_SQL."_moveStockLocation",
		"$\${$_log_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_type]",
		"ARRAY[$_it_location_from]",
		"ARRAY[$_it_location_to]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_it_remark]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_location]",
		"ARRAY[$_ed_it_type]",
		"ARRAY[$_ed_it_date]",
		"ARRAY[$_ed_it_qty]"
	);

	if(isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_move_location.php?_show_message=error");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/input_move_location.php?_show_message=true");
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

	//Check is has been complete input E/D
	var o		  = window.document.frmInsert;
	var oCheck	  = o.all.tags("INPUT");
	var countItem = window.rowPosition.rows.length;
	var countED	  = window.EDPosition.rows.length;
	var sum_input_ed_stock	= new Array();

	for (var i = 0; i < oCheck.length; i++) {
		if (oCheck[i].type == "hidden" && oCheck(i).name == "_it_code[]" && oCheck(i+5).value == "true") {
			if(countED <= 0) {alert("Please complete E/D for each related item");return;}
			var chk_item = '';
			for (var j = 0; j < oCheck.length; j++) {
				if (oCheck[j].type == "hidden" && oCheck(j).name == "_ed_it_code[]" && trim(oCheck(j).value) == trim(oCheck(i).value)) {
					if(chk_item != oCheck(j).value) {
						sum_input_ed_stock[trim(oCheck(i).value)] = parseFloat(removecomma(oCheck(j+5).value));
					} else if(chk_item == oCheck(j).value) {
						sum_input_ed_stock[trim(oCheck(i).value)] += parseFloat(removecomma(oCheck(j+5).value));
					}
					chk_item = oCheck(j).value;
				}
			}
		}
	}

	//Check move qty with inputed E/D
	for (var i = 0; i < oCheck.length; i++) {
		if (oCheck[i].type == "hidden" && oCheck(i).name == "_it_code[]" && oCheck(i+5).value == "true") {
			if(parseFloat(removecomma(oCheck(i+7).value)) != sum_input_ed_stock[trim(oCheck(i).value)]) {
				var chk_qty  = parseFloat(removecomma(oCheck(i+7).value));

				if(sum_input_ed_stock[trim(oCheck(i).value)] == null) {
					var ed_qty	 = 0;
				} else {
					var ed_qty	 = sum_input_ed_stock[trim(oCheck(i).value)];				
				}
				var diff_qty = chk_qty - ed_qty;
				if(diff_qty < 0) 		{diff_qty=diff_qty*-1;}
				else if(diff_qty == '') {diff_qty=0;}

				alert(
					"Please check E/D list with for item code "+ trim(oCheck(i).value) + "\n\n" +
					"Inputed move qty : " + numFormatval(chk_qty+'',2) + "\n" +
					"Inputed e/d qty    : " + numFormatval(ed_qty+'',2) + "\n" +
					".:. Different = " + numFormatval(diff_qty+'',2)
				);
				return;
			}
		}
	}

	if (verify(f)) {
		if(confirm("Are you sure to move location of selected item?")) {
			f.submit();
		}
	}
}

//Open window for search item
var wSearchItem;
function fillItem() {
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;

	wSearchItem = window.open("./p_list_move_stock.php",'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem() {

	var f   = window.document.frmInsert;
	var f2	= wSearchItem.document.frmCreateItem;
	var oTR	= window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value) + '-' +f2.elements[5].value) {
			if(f2.elements[5].value==1) 	 {type='vat';}
			else if(f2.elements[5].value==1) {type='non vat';}
			alert("Item ["+trim(f2.elements[0].value)+"] "+f2.elements[1].value+" type "+type+" already exist!");
			return;
		}
	}

	for (var i=0; i<10; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // _it_code
				var code	= f2.elements[0].value;
				var item	= f2.elements[1].value;
				var loc		= f2.elements[9].value;
				var type	= f2.elements[5].value;

				if(f2.elements[8].value=='true') {
					oTD[i].innerHTML	= "<a href=\"javascript:insertED('"+code+"','"+item+"',"+loc+","+type+")\"><b>"+f2.elements(i).value+"</b></a>";
				} else {
					oTD[i].innerText	= f2.elements[0].value;
				}
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

			case 2: // _it_desc
				oTD[i].innerText		= f2.elements[2].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_desc[]";
				oTextbox[i].value		= f2.elements[2].value;
				break;

			case 3: // _it_type
				if(f2.elements[5].value == 1) {
					var type			= 1;
					oTD[i].innerText	= 'VAT';
					oTD[i].align		= 'center';
				} else if(f2.elements[5].value == 2) {
					var type			= 2;
					oTD[i].innerText	= 'NON';
					oTD[i].align		= 'center';
				}

				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_type[]";
				oTextbox[i].value		= type;
				break;

			case 4: // _it_location_from
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

				var loc					= f2.elements[9].value;
				oTD[i].innerText		= wh_name;
				oTD[i].align			= 'center';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_location_from[]";
				oTextbox[i].value		= loc;
				break;

			case 5: // image
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_ed[]";
				oTextbox[i].value		= f2.elements[8].value;
				oTD[i].align			= "center";
				oTD[i].innerHTML		= "<img src=\"../../_images/icon/arrow_right_disabled.gif\">";
				break;

			case 6: // _it_location_to
				var wh_name = '';
				if (f._function.value == 'IDC') {
					if(f2.elements[10].value == 1) {
						wh_name = 'IDC';
					} else if(f2.elements[10].value == 2) {
						wh_name = 'DNR';
					}
				} else if (f._function.value == 'MED') {
					if(f2.elements[10].value == 1) {
						wh_name = 'MED';
					} 
				}

				var loc					= f2.elements[9].value;
				oTD[i].innerText		= wh_name;
				oTD[i].align			= 'center';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_location_to[]";
				oTextbox[i].value		= loc;
				break;

			case 7: // _it_qty
				oTD[i].innerText		= numFormatval(f2.elements[4].value+'',2);
				oTD[i].align			= 'right';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_qty[]";
				oTextbox[i].value		= numFormatval(f2.elements[4].value+'',2)
				break;

			case 8: // _it_remark
				oTextbox[i].type		= "text";
				oTextbox[i].name		= "_it_remark[]";
				oTextbox[i].className	= "fmt";
				oTextbox[i].value		= f2.elements[11].value;
				break;

			case 9: // [del]
				oTD[i].align			= "center";
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + '-' +f2.elements[5].value + "')\"><img src=\"../../_images/icon/delete.gif\" width=\"12px\"></a>";
				break;

		}
		if (i<9) {oTD[i].appendChild(oTextbox[i]);}
		oTR.id = trim(f2.elements[0].value) + '-' +f2.elements[5].value;
		oTR.appendChild(oTD[i]);
	}
	window.rowPosition.appendChild(oTR);
}

function deleteItem(code) {
	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);

		if(oRow.cells(2).innerText=='IDC')		{ var loc=1; }
		else if(oRow.cells(2).innerText=='MED')	{ var loc=1; }
		else if(oRow.cells(2).innerText=='DNR') { var loc=2; }
		if(oRow.cells(3).innerText=='VAT')		{ var type=1; }
		else if(oRow.cells(3).innerText=='NON') { var type=2; }

		if (oRow.id == trim(code) + + loc + '-' +  oRow.cells(4).innerText) {
			var n = window.EDPosition.removeChild(oRow);
			count = count - 1;
			i = i - 1;
		}
	}

	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == code) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
		}
	}
}

var wInputED;
function insertED(code,item,loc,type) {

	var item = item;
	var loc	 = loc;
	var type = type;
	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 290) / 2;

	wInputED = window.open(
		'./p_input_ed.php?_act=move_location&_code='+code+'&_item='+item+'&_type='+type+'&_loc='+loc,
		'move_location',
		'scrollbars,width=450,height=290,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

function createED() {
	var o	= window.document.frmInsert;
	var f2	= wInputED.document.frmInsert;

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();
	var d	= parseDate(f2.elements[4].value, 'prefer_euro_format');

	var count = EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value) + '-' + f2.elements[3].value + f2.elements[2].value + '-' + formatDate(d, 'NNN-yyyy')) {
			alert("Item ["+trim(f2.elements[0].value)+"] "+f2.elements[1].value+" with choosen E/D already exist!");
			return;
		}
	}

	for (var i=0; i<7; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // CODE
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // IT MODEL NO
				oTD[i].innerText	= f2.elements[1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_model_no[]";
				oTextbox[i].value	= f2.elements[1].value;
				break;

			case 2: // WH LOCATION
				var wh_name = '';
				if (o._function.value == 'IDC') {
					if(f2.elements[2].value == 1) {
						wh_name = 'IDC';
					} else if(f2.elements[2].value == 2) {
						wh_name = 'DNR';
					}
				} else if (o._function.value == 'MED') {
					if(f2.elements[2].value == 1) {
						wh_name = 'MED';
					} 
				}
				var loc					= f2.elements[2].value;
				oTD[i].innerText		= wh_name;
				oTD[i].align			= 'center';
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_location_to[]";
				oTextbox[i].value		= loc;
				break;

			case 3: // TYPE
				if(f2.elements[3].value==1) {
					oTD[i].innerText	= 'VAT';
				} else if(f2.elements[3].value==2) {
					oTD[i].innerText	= 'NON';
				}
				oTD[i].align		= "center";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_type[]";
				oTextbox[i].value	= f2.elements[3].value;
				break;

			case 4: // E/D
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_date[]";
				oTextbox[i].value	= f2.elements[4].value;
				break;

			case 5: // QTY
				oTD[i].innerText	= f2.elements[7].value;
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_qty[]";
				oTextbox[i].value	= f2.elements[7].value;
				break;

			case 6: // DELETE
				oTD[i].innerHTML	= "<a href=\"javascript:deleteED('" + trim(f2.elements[0].value) + '-' + f2.elements[3].value + f2.elements[2].value + '-' + formatDate(d, 'NNN-yyyy') + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align		= "center";
				break;
		}
		if (i!=6) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value) + '-' + f2.elements[3].value + f2.elements[2].value + '-' + formatDate(d, 'NNN-yyyy');
		oTR.appendChild(oTD[i]);
	}
	window.EDPosition.appendChild(oTR);
} 

function deleteED(idx) {
	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.EDPosition.removeChild(oRow);
			count = count - 1;
			break;
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
		<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] MOVE STOCK LOCATION</h3>
<?php
if($isShowMessage != '') {
	echo "<hr>";

	if($isShowMessage == "true") {
		echo "<span style=\"font-family:Courier;color:#015396\">\n";
		echo "\t<img src=\"../../_images/icon/check.gif\"> Success move stock location\n";
		echo "</span><br /><br />\n";
	} else if($isShowMessage == "error") {
		echo "<span style=\"font-family:Courier;color:red\">";
		echo "\t<img src=\"../../_images/icon/alert.gif\"> Database error. Can't move stock location";
		echo "</span><br /><br />";
	}
	echo "<div align=\"right\">";
	echo "\t<a href=\"input_move_location.php\" style=\"font-family:Courier;color:#015396\">Close this section <img src=\"../../_images/icon/close_mini.gif\"></a>";
	echo "</div><hr><br /><br />";
}
?>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type='hidden' name='_function' value='<?php echo ZKP_SQL ?>'>
<span class="bar_bl">ITEM LIST</span> <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l" cellspacing="1">
	<thead>
		<tr height="35px">
			<th width="7%" rowspan="2">CODE</th>
			<th width="15%" rowspan="2">ITEM NO</th>
			<th rowspan="2">DESCRIPTION</th>
			<th width="21%" colspan="4">ITEM DESCRIPTION</th>
			<th width="7%" rowspan="2">QTY</th>
			<th width="15%" rowspan="2">REMARK</th>
			<th width="5%" rowspan="2">DEL</th>
		</tr>
		<tr>
			<th width="6%">TYPE</th>
			<th width="6%">FROM</th>
			<th width="3%"><img src="../../_images/icon/arrow_right.gif"></th>
			<th width="6%">TO</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
</table><br />
<span class="bar">E/D INFORMATION</span>
<table width="70%" class="table_l">
 	<thead>
		<tr height="25px">
			<th width="15%">CODE</th>
			<th>ITEM NO</th>
			<th width="10%">SOURCE</th>
			<th width="10%">TYPE</th>
			<th width="20%">E/D</th>
			<th width="15%">QTY</th>
			<th width="5%"></th>
		</tr>
	</thead>
	<tbody id="EDPosition">
	</tbody>
</table><br />
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkForm()'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save move location"> &nbsp; Save</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_move_location.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel move location"> &nbsp; Cancel</button>
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