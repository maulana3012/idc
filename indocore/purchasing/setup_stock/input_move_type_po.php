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
$left_loc	= "input_move_type_po.php";
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

	f.cboLocation.onchange = function() {
		if(f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.submit();
	}

	f.cboType.onchange		= f.cboLocation.onchange;

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
<input type='hidden' name='p_mode' value='change_type_po'>
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
<?php require_once APP_DIR . "_include/purchasing/report/setup_stock/rpt_list_move_type_po.php" ?>
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
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=1) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=1) 
  END AS qty_1,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=2) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=1 and stk_type=2) 
  END AS qty_2,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=1) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=1) 
  END AS qty_3,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=2) is null then 0
	ELSE (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_shadow WHERE it_code=it.it_code and stk_wh_location=2 and stk_type=2) 
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
				if(oCheck(i+4).value=='1' && oCheck(i+5).value=='1')	  {stock_id=1;type='vat';loc='Medisindo';}
				else if(oCheck(i+4).value=='1' && oCheck(i+5).value=='2') {stock_id=2;type='non';loc='Medisindo';}
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