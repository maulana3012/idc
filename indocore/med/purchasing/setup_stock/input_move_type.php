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
$left_loc	= "list_borrow_by_item.php";
$s_mode 	= (isset($_GET['s_mode']) && $_GET['s_mode'] != "") ? $_GET['s_mode'] : "period";
$_loc		= isset($_GET['cboLocation']) ? $_GET['cboLocation'] : "all";
$_type		= isset($_GET['cboType']) ? $_GET['cboType'] : "all";
$_code		= isset($_GET['_code']) ? $_GET['_code'] : "";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-604800);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {
	if(isset($_GET['some_date'])) {
		$some_date = $_GET['some_date'];
	} else {
		$some_date = date('j-M-Y');
		$_GET['cboDate'] = "0";
	}

	$period_from 		= "";
	$period_to 			= "";
}

//PROCESS FORM
require_once APP_DIR . "_include/purchasing/tpl_process_setup_form.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
/*
var wInputED;
function insertED(code,item,type) {

	var loc	 = '<?php echo $_loc ?>';
	var type = '<?php echo $_type ?>';
	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 290) / 2;

	if(window.document.frmMovetype.btnMove.disabled) { return;}

	wInputED = window.open(
		'./p_input_ed.php?_act=move_type_rev&_code='+code+'&_item='+item+'&_type='+type+'&_type='+type+'&_loc='+loc,
		'move_type_rev',
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

	//Check has same Item and E/D
	var count = EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)+'-'+ f2.elements[4].value) {
			alert("Same Item with inputed E/D code already exist!");
			return false;
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
				if(f2.elements[2].value==1) {
					oTD[i].innerText	= 'IDC';
				} else if(f2.elements[2].value==2) {
					oTD[i].innerText	= 'DNR';
				}
				oTD[i].align		= "center";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_location[]";
				oTextbox[i].value	= f2.elements[2].value;
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
				oTD[i].innerHTML	= "<a href=\"javascript:deleteED('"+ trim(f2.elements[0].value)+'-'+ f2.elements[4].value +"')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align		= "center";
				break;
		}
		if (i!=6) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value)+'-'+ f2.elements[4].value;
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
}*/
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
		<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] MOVE STOCK TYPE</h3>
<form name="frmSearch">
<table width="100%" class="table_layout">
	<tr>
		<td width="80%"> </td>
		<td> LOCATION </td>
		<td> TYPE </td>
		<td> DOCUMENT DATE </td>
		<td> DOCUMENT PERIOD </td>
	</tr>
	<tr>
		<td></td>
		<td>
			<select name="cboLocation">
				<option value="all">==ALL==</option>
<?php
$wh = array($cboFilter[3]['purchasing'][ZKP_SQL], count($cboFilter[3]['purchasing'][ZKP_SQL]));
for($i=0; $i<$wh[1]; $i++) {
	$v = (intval($_location)==intval($wh[0][$i][0]))?' checked':'';
	echo "\t\t\t<option value=\"".$wh[0][$i][0]."\">".$wh[0][$i][1]."</option>";
}
?>
			</select>
		</td>
		<td>
			<select name="cboType">
				<option value="all">==ALL==</option>
				<option value="1">VAT</option>
				<option value="2">NON</option>
			</select>
		</td>
		<td valign="middle">
			<input type="hidden" name="s_mode">
			<a href="javascript:setFilterDate('date',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous date"> </a>
			<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
			<a href="javascript:setFilterDate('date',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next date"> </a>
		</td>
		<td>
			<a href="javascript:setFilterDate('period',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous month"> </a>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
			<a href="javascript:setFilterDate('period',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next month"> </a>
		</td>
	</tr>
</table><br />
</form>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboLocation, "<?php echo isset($_GET['cboLocation']) ? $_GET['cboLocation'] : "all"?>");
	setSelect(f.cboType, "<?php echo isset($_GET['cboType']) ? $_GET['cboType'] : "all"?>");

	function setFilterDate(status, value){
		f.s_mode.value = status;
		if(status == 'date') {
			var date = parseDate(f.some_date.value, 'prefer_euro_format');
			setFilterDateCalc(date, value, f.some_date);
			f.period_from.value = '';
			f.period_to.value = '';
		} else if(status == 'period') {
			var d = new Date(ts);
			setFilterPeriodCalc(d, value, f.period_from, f.period_to);
		}
		f.submit();
	}
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.submit();
		}
	}
</script>
<form name='frmMovetype' method='POST'>
<input type='hidden' name='p_mode' value='change_type'>
<input type='hidden' name='_location' value="<?php echo $_loc ?>">
<input type='hidden' name='_type_item' value="<?php echo $_type ?>">
<input type='hidden' name='_it_code'>
<div id="itemList"></div>
<table class="table_b" width="100%">
	<tr>
    	<th width="25%" style="font-size:0.8em">REFERENCE DOCUMENT</th>
        <td><input type="text" name="_doc_number" class="fmt" style="font-size:1.5em; color:darkblue; width:100%"></td>
    	<th width="25%" style="font-size:0.8em">REFERENCE DATE</th>
		<td><input type="text" name="_doc_date" class="fmtd" style="font-size:1.5em; color:darkblue; width:100%"></td>
    </tr>
</table>
<?php require_once APP_DIR . "_include/purchasing/report/setup_stock/rpt_list_move_type.php" ?>
<!--<table width="75%" class="table_l">
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
</table><br />-->
<table width="100%" class="table_layout">
	<tr>
		<td><input type="checkbox" name="chkAll" onClick="checkAll(this.checked)"><span class="comment">check all</span></td>
		<td align="right">
			<button name='btnMove' class='input_btn' style='width:80px;'><img src="../../_images/icon/setting_mini.gif" align="middle"> &nbsp; MOVE</button>&nbsp;
			<button name='btnSummarize' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Summarize</button>
		</td>
	</tr>
</table><br />
</form>
<?php
//current stock
$items = (count($items)>0) ? implode("','", $items) : "";
$stock_sql = "
SELECT
  it_code,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=1) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=1) 
  END AS qty_1,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=2) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=2) 
  END AS qty_2,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=1) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=1) 
  END AS qty_3,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=2) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=2) 
  END AS qty_4
FROM ".ZKP_SQL."_tb_item AS it
WHERE it_code IN ('".$items."')
ORDER BY it_code";
$stock_res	=& query($stock_sql);
?>
<script language="javascript" type="text/javascript">
<?php
echo "var stock = new Array();\n";
while ($rows =& fetchRow($stock_res, 0)) {
	printf("stock['%s'] = [%s,%s,%s,%s,%s];\n",
		addslashes($rows[0]),	//item
		'0',
		$rows[1],	//idc, vat
		$rows[2],	//idc, non
		$rows[3],	//dnr, vat
		$rows[4]	//dnr, non
	);
}
?>

	function checkAll(o) {
		var oCheck = window.document.frmMovetype.tags("INPUT");
	
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck[i].name == "chkBorIdx[]" && oCheck(i).disabled == false) {
				oCheck[i].checked = o;
			}
		}
	}
/*
	function addItem(o, val) {
		var f = window.document.frmMovetype;
		var item_l = document.getElementById('itemList');
		var count = $("#itemList div").length;
		var is_isset = false;

		var oI	 = document.getElementById('itemList').getElementsByTagName('INPUT');;
		for (var i = 0; i < count; i++) {
			if (oI(i).name == "it_code[]" && trim(oI(i).value) == trim(val)) {
				is_isset = true;
			}
		}

		if (o.checked) {
			if(is_isset == false) {
				var div  = document.createElement("DIV");
				var el = document.createElement('input');
				el.type = 'text'; el.name = 'it_code[]'; el.value = val;
				div.id = val;
				div.appendChild(el);
				item_l.appendChild(div);
			}
		} else if (o.checked == false) {
			if(is_isset) {
				var d = document.getElementById('itemList');
				var d2 = document.getElementById(val);
				window.itemList.removeChild(d2);
			}
		}
	}
*/
	window.document.frmMovetype.btnSummarize.onclick = function() {
		var oCheck		 = window.document.frmMovetype.tags("INPUT");
		var keyword		 = '';
		var counter		 = 0;
	
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkBorIdx[]" && oCheck(i).checked) {
				if(keyword == '') {
					keyword = oCheck[i].value;
				} else {
					keyword = keyword + ', ' + oCheck[i].value;
				}
			}
		}
	
		if(keyword == '') {
			alert("You haven't checked any DO Code.\nPlease check first");
			return;
		}
	
		var x = (screen.availWidth - 550) / 2;
		var y = (screen.availHeight - 470) / 2;
		var win = window.open(
			'./p_summary_borrow.php?_code='+ keyword,
			'',
			'scrollbars,width=550,height=470,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	}

	window.document.frmMovetype.btnMove.onclick = function() {
		var o			 = window.document.frmMovetype;
		var oCheck		 = o.all.tags("INPUT");
		var selectedItem = new Array();
		var counter		 = 0;

		if(document.frmSearch.cboLocation.value == 'all' && document.frmSearch.cboType.value == 'all') {
			alert("Location and Type must be choosed");
			return;
		}

		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkBorIdx[]" && oCheck(i).checked) {
				selectedItem[counter++] = oCheck[i].value;
			}
		}
		if(selectedItem.length <= 0) {
			alert("Please select item you want to return");
			return;
		}

		//Sum checked qty per it_code
		var sum_chk_stock	= new Array();
		var it_chk_stock	= new Array();
		var count_chk_stock = 0;
		var chk_item	  = '';
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkBorIdx[]" && oCheck(i).checked) {
				if(chk_item != oCheck(i+1).value) {
					sum_chk_stock[trim(oCheck(i+1).value)]	= parseFloat(oCheck(i+6).value);
					it_chk_stock[count_chk_stock]			= trim(oCheck(i+1).value);
					count_chk_stock += 1;
				} else if(chk_item == oCheck(i+1).value) {
					sum_chk_stock[trim(oCheck(i+1).value)] += parseFloat(oCheck(i+6).value);
				}
				chk_item = oCheck(i+1).value;
			}
		}

		//Check is there available Stock
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkBorIdx[]" && oCheck(i).checked) {

				var stock_id = 0; var type	 = ''; var loc		 = '';

				//loc, type
				if(oCheck(i+4).value=='1' && oCheck(i+5).value=='1')	  {stock_id=2;type='non';loc='Medisindo';}
				else if(oCheck(i+4).value=='1' && oCheck(i+5).value=='2') {stock_id=1;type='vat';loc='Medisindo';}
//				else if(oCheck(i+4).value=='2' && oCheck(i+5).value=='1') {stock_id=4;type='non';loc='DNR';}
//				else if(oCheck(i+4).value=='2' && oCheck(i+5).value=='2') {stock_id=3;type='vat';loc='DNR';}

				if(parseFloat(stock[oCheck(i+1).value][stock_id]) < parseFloat(oCheck(i+6).value)) {
					alert(
					".:. You cannot return borrow stock for item [" +  trim(oCheck(i+1).value) +"] "+ oCheck(i+2).value + " .:.\n\n" + 
					"Check current stock for :\n" +
					"Code : ["+ trim(oCheck(i+1).value) +"] "+ oCheck(i+2).value + "\n" +
					"Current "+type+" stock in "+loc+" stock qty : "+numFormatval(stock[oCheck(i+1).value][stock_id]+'',2)+" (Less than expected return qty)\n" +
					"Current selected return borrow qty        : "+numFormatval(sum_chk_stock[trim(oCheck(i+1).value)]+'',2));
					return;
				}
			}
		}
/*
		//Check is has been complete input E/D
		var countED = window.EDPosition.rows.length;
		var sum_input_ed_stock		= new Array();
		var counted_item = '';
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkBorIdx[]" && oCheck(i).checked) {
				if(oCheck(i+3).value == 't' && counted_item != oCheck(i+1).value) {
					if(countED <= 0) {alert("Please complete E/D for each related item");return;}
					var chk_item = '';
					for (var j = 0; j < oCheck.length; j++) {
						if (oCheck[j].type == "hidden" && oCheck(j).name == "_ed_it_code[]" && trim(oCheck(j).value) == trim(oCheck(i+1).value)) {
							if(chk_item != oCheck(j).value) {
								sum_input_ed_stock[trim(oCheck(i+1).value)] = parseFloat(oCheck(j+5).value);
							} else if(chk_item == oCheck(j).value) {
								sum_input_ed_stock[trim(oCheck(i+1).value)] += parseFloat(oCheck(j+5).value);
							}
							chk_item = oCheck(j).value;
						}
					}
					counted_item = oCheck(i+1).value;
				}
			}
		}

		//Check qty for return with inputed E/D
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkBorIdx[]" && oCheck(i).checked) {
				if(oCheck(i+3).value == 't') {
					var chk_qty  = sum_chk_stock[trim(oCheck(i+1).value)];
					if(sum_input_ed_stock[trim(oCheck(i+1).value)] == null) {
							 var ed_qty	 = 0;
					} else { var ed_qty	 = sum_input_ed_stock[trim(oCheck(i+1).value)]; }

					if(chk_qty != ed_qty) {
						var diff_qty = chk_qty - ed_qty;
						if(diff_qty < 0) {diff_qty=diff_qty*-1;}
						alert(
							"Please check E/D list with return borrow qty for item code ["+ trim(oCheck(i+1).value) + "] "+ oCheck(i+2).value +"\n\n" +
							"Selected return qty : " + numFormatval(chk_qty+'',2) + "\n" +
							"Inputed e/d qty      : " + numFormatval(ed_qty+'',2) + "\n" +
							".:. Different = " + numFormatval(diff_qty+'',2)
						);
						return;
					}
				}
			}
		}

		//Check invalid E/D qty ==================================
		var sum_input_ed_stock_II = new Array();
		for (var i = 0; i < oCheck.length; i++) {
			var item_checked = false;
			if (oCheck[i].type == "hidden" && oCheck(i).name == "_ed_it_code[]") {
				for (var j = 0; j < oCheck.length; j++) {
					if (oCheck[j].type == "checkbox" && oCheck(j).name == "chkBorIdx[]" && oCheck(j).checked) {
						if(trim(oCheck(j+1).value) == trim(oCheck(i).value)) {
							item_checked = true;
						}
					}
				}
				if(item_checked == false) {
					alert("Please check inputed E/D for item ["+ trim(oCheck(i).value) +"] "+oCheck(i+1).value+"\n"+
							"You haven't checked any invoice to return for selected E/D qty");
					return;
				}
			}
		}
*/
		//Make a summary about qty that will be return,
		var return_code = new Array();
		var return_item = new Array();
		var return_qty	= new Array();
		var prev_item	= '';
		var x			= 0;
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkBorIdx[]" && oCheck(i).checked) {
				if(trim(oCheck(i+1).value) != prev_item) {
					return_code[x]	= trim(oCheck(i+1).value);
					return_item[x]	= oCheck(i+2).value;
					return_qty[x]	= sum_chk_stock[trim(oCheck(i+1).value)];
					x += 1;
				}
				prev_item = trim(oCheck(i+1).value);
			}
		}

		var len = return_code.length;
		var print_summary = '';
		var x = 1;
		var it_code = ''
		for (var i = 0; i < len; i++) {
			print_summary = print_summary + x + ". [" + return_code[i] + "] " + return_item[i] + ', ' + return_qty[i] + " qty\n";
			var it_code = it_code +'$$,$$'+ return_code[i];
			x += 1;
		}
		it_code = it_code.substr(3);
		it_code = it_code + '$$';
		o._it_code.value = it_code;

		alert("Summary selected item :\n\n" + print_summary);

		if(selectedItem.length > 0) {
			if (confirm("Are you sure to return the selected item?")) {
				if(verify(o)){
					o.submit();
				}
			}
		}
	}
</script>
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