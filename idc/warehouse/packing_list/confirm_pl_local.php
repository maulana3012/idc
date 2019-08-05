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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "summary_outstanding_by_supplier.php";
if ($_GET['_code']=='' && $_GET['_pl_no'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_pl_by_supplier.php");
} else {
	$_code	= $_GET['_code'];
	$_pl_no	= $_GET['_pl_no'];
}
$_idx_pl = 'local';

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_packing_list.php";
require_once "detail_incoming_m.php";

//-------------------------------------------------------------------------------------------- DEFAULT PROCESS
$col[0] = getPLConfirm('Local', $_code, $_pl_no, 'info'); 
$col[1] = getPLConfirm('Local', $_code, $_pl_no, 'item'); 
$col[2] = getPLConfirm('Local', $_code, $_pl_no, 'in_item');

if (count($col[0]) <= 0) {
	goPage('summary_outstanding_by_supplier.php');
}
/*
echo "<pre>";
var_dump($col);
echo "</pre>";
exit;
*/
//Incoming PL
$rd	= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$numRow = numQueryRows($col[2]);
while($col_in =& fetchRowAssoc($col[2])) {

	$rd[] = array(
		$col_in['inlc_idx'],	//0
		$col_in['checked_date'],//1
		$col_in['invoice_no'],	//2
		$col_in['it_code'], 	//3
		$col_in['it_model_no'],	//4
		$col_in['it_desc'],	//5
		$col_in['init_qty'], 	//6
		$col_in['inpl_has_ed'],	//7
		$col_in['go_page']	//8
	);

	//1st grouping
	if($cache[0] != $col_in['inlc_idx']) {
		$cache[0] = $col_in['inlc_idx'];
		$group0[$col_in['inlc_idx']] = array();
	}

	if($cache[1] != $col_in['it_code']) {
		$cache[1] = $col_in['it_code'];
	}

	$group0[$col_in['inlc_idx']][$col_in['it_code']] = 1;
}
$g_total = 0;
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
function checkQty(value, i, part){
	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 8;
	var idx_qty		= 8;						/////
	var idx_in_wh	= idx_qty+2;
	var idx_arrived	= idx_qty+3;
	var idx_on_deli	= idx_qty+4;
	var sumOfQty	= 0;
	var e = window.document.frmInsert.elements;

	var arrived = parseFloat(removecomma(e(idx_arrived+i*numInput).value));
	var on_deli = parseFloat(removecomma(e(idx_on_deli+i*numInput).value));
	var amount 	= arrived + on_deli;

	if(value == 0) {
		alert("All qty in this item has been confirmed");
		e(idx_arrived+i*numInput).value = 0;
		e(idx_on_deli+i*numInput).value = 0;
		return;
	}else if(arrived > value) {
		alert("Maximum qty for this item is " + addcomma(value) +" pcs.\n Please check the amount again");
		e(idx_arrived+i*numInput).value = addcomma(value);
		e(idx_on_deli+i*numInput).value = 0;
		updateAmount();
		return;
	} else {
		if(part == 1) {
			e(idx_on_deli+i*numInput).value = addcomma(value - arrived);
		} else if(part == 2) {
			e(idx_arrived+i*numInput).value = addcomma(value - on_deli);
		}
		updateAmount();
		return;
	}
}

function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 8;
	var idx_qty		= 8;					/////
	var idx_in_wh	= idx_qty + 2;
	var idx_arrived	= idx_qty + 3;
	var idx_on_deli	= idx_qty + 4;

	var sumOfQty	 = 0;
	var sumOfInWH	 = 0;
	var sumOfArrived = 0;
	var sumOfOnDeli  = 0;

	var e = window.document.frmInsert.elements;

	for (var i=0; i<numItem; i++) {
		var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));
		var in_wh	= parseFloat(removecomma(e(idx_in_wh+i*numInput).value));
		var arrived = parseFloat(removecomma(e(idx_arrived+i*numInput).value));
		var on_deli = parseFloat(removecomma(e(idx_on_deli+i*numInput).value));

		sumOfQty		+= qty;
		sumOfInWH		+= in_wh;
		sumOfArrived	+= arrived;
		sumOfOnDeli		+= on_deli;
	}

	f.totalQty.value		= addcomma(sumOfQty);
	f.totalInWH.value		= addcomma(sumOfInWH);
	f.totalArrrived.value	= addcomma(sumOfArrived);
	f.totalOnDeli.value		= addcomma(sumOfOnDeli);

	if(parseFloat(removecomma(f.totalQty.value)) == parseFloat(removecomma(f.totalInWH.value))) {
		window.document.all.btnConfirm.disabled = true;
	}
}

var wInputED;
function insertED(code, i) {

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var oRow		= window.rowPosition.rows(i);
	var item		= oRow.cells(1).innerText;

	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 200) / 2;

	wInputED = window.open(
		'./p_input_ed.php?_code='+code+'&_item='+item, 'wSearchED',
		'scrollbars,width=450,height=200,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wInputED.focus();
}

function createED() {
	var o	= window.document.frmConfirm;
	var f2	= wInputED.document.frmInsert;

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();
	var d	= parseDate(f2.elements[2].value, 'prefer_euro_format');

	var count = window.EDPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.EDPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)+'-'+trim(f2.elements[2].value)) {
			alert("Item ["+trim(f2.elements[0].value)+"] "+ f2.elements[1].value +" for E/D "+ formatDate(d, 'NNN-yyyy') + " already exist!");
			return;
		}
	}

	for (var i=0; i<5; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");

		switch (i) {
			case 0: // ed_it_code
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // ed_it_model_no
				oTD[i].innerText	= f2.elements[1].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_model_no[]";
				oTextbox[i].value	= f2.elements[1].value;
				break;

			case 2: // ed_it_date
				oTD[i].innerText	= formatDate(d, 'NNN-yyyy');
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_date[]";
				oTextbox[i].value	= formatDate(d, '1-NNN-yyyy');
				break;

			case 3: // ed_it_qty
				oTD[i].innerText	= numFormatval(f2.elements[3].value+'',0);
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_qty[]";
				oTextbox[i].value	= parseFloat(removecomma(f2.elements[3].value));
				break;

			case 4: // DELETE
				oTD[i].innerHTML	= "<a href=\"javascript:deleteED('"+f2.elements[0].value+'-'+trim(f2.elements[2].value)+"')\"><img src=\"../../_images/icon/delete.gif\" width=\"12px\"></a>";
				oTD[i].align		= "center";
				break;
		}
		if (i!=4) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value)+'-'+trim(f2.elements[2].value);
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
		}
	}
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="updateAmount()">
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
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CONFIRM INCOMING PL<br />
</strong>
<small class="comment">* Source by PO Local</small>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value="confirm_PL_local">
<input type="hidden" name="_po_code" value="<?php echo $col[0]['po_code']?>">
<input type="hidden" name="_pl_no" value="<?php echo $col[0]['pl_no']?>">
<input type="hidden" name="_sp_code" value="<?php echo $col[0]['sp_code']?>">
<input type="hidden" name="_pl_type" value="<?php echo $col[0]['po_type']?>">
<span class="bar_bl">PL INFORMATION</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PL NO</th>
		<td width="22%"><b><?php echo $col[0]["po_code"] ?>&nbsp; &nbsp;#<?php echo $col[0]["pl_no"] ?></b></td>
		<th width="7%">
			<a href="../purchasing/revise_po.php?_code=<?php echo $_code ?>" target="_blank"><img src="../../_images/icon/list_mini.gif" alt="View detail PO"></a>
		</th>
		<td width="5%"></td>
		<th width="15%">PL DATE</th>
		<td><?php echo date('j-M-Y', strtotime($col[0]["pl_date"])) ?></td>
	</tr>
	<tr>
		<th width="15%">ISSUED BY</th>
		<td colspan="3"><?php echo $col[0]["pl_issued_by"] ?></td>
		<th>DELIVERY DATE</th>
		<td><?php echo date('j-M-Y', strtotime($col[0]["pl_delivery_date"])) ?></td>
	</tr>
	<tr>
		<th width="15%">SUPPLIER</th>
		<td colspan="5"><?php echo '['.$col[0]["sp_code"].'] '.$col[0]["po_sp_name"] ?></td>
	</tr>
</table><br />	
	<span class="bar_bl">CONFIRM ARRIVAL ITEMS</span>
	<table width="100%" class="table_box" cellspacing="1">
		<thead>
			<tr>
				<th rowspan="2" width="5%">CODE</th>
				<th rowspan="2" width="17%">ITEM</th>
				<th rowspan="2">DESC</th>
				<th rowspan="2" width="10%">QTY</th>
				<th rowspan="2" width="7%">UNIT</th>
				<td rowspan="2" width="1%"></td>
				<th colspan="3">STATUS</th>
			</tr>
			<tr>
				<th width="8%">IN WH</th>
				<th width="8%">ARRIVED</th>
				<th width="8%">PENDING</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
$i = 0;
$code = '';
while($items =& fetchRow($col[1])) {
?>
			<tr id="<?php echo trim($items[1])?>">
				<td>
					<?php
					if($items[2]=='f') {echo $items[1]."\n";}
					else {echo "<a href=\"javascript:insertED('".trim($items[1])."',$i)\"><span class=\"bar\">".trim($items[1])."</span></a>\n";}
					?>
					<input type="hidden" name="_it_code[]" value="<?php echo $items[1]?>">
					<input type="hidden" name="_it_model_no[]" value="<?php echo $items[3]?>">
					<input type="hidden" name="_it_ed[]" value="<?php echo ($items[2] == 't') ? 'true' : 'false'?>">
				</td>
				<td><?php echo $items[3]?></td>
				<td><?php echo cut_string($items[4],60)?></td>
				<td><input type="text" name="_plit_qty[]" value="<?php echo trim($items[5])?>" style="width:100%" class="fmtn" readonly></td>
				<td><input type="text" name="_plit_unit[]" value="<?php echo $items[6]?>" style="width:100%" class="fmt" readonly></td>
				<td></td>
				<td><input type="text" name="_plit_in_wh[]" value="<?php echo number_format($items[5]-$items[7])?>" style="width:100%" class="reqn" readonly></td>
				<td><input type="text" name="_plit_arrived[]" value="<?php echo number_format($items[7])?>" style="width:100%" class="reqn" onKeyUp="formatNumber(this,'dot')" onBlur="checkQty(<?php echo $items[7].",".$i.",1" ?>)"></td>
				<td><input type="text" name="_plit_on_deli[]" value="0" style="width:100%" class="reqn" onKeyUp="formatNumber(this,'dot')" readonly></td>
			</tr>
<?php
	$i++;
}
?>
		</tbody>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th align="right">GRAND TOTAL</th>
			<th width="10%"><input name="totalQty" type="text" class="fmtn" style="width:100%" readonly></th>
			<th width="6%">&nbsp;</th>
			<td width="1%">&nbsp;</td>
			<th width="8%"><input name="totalInWH" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="8%"><input name="totalArrrived" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="8%"><input name="totalOnDeli" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
	</table><br />
	<span class="bar_bl">DETAIL ITEM PER E/D</span>
	<table width="50%" class="table_l">
		<thead>
			<tr height="25px">
				<th width="15%">CODE</th>
				<th>ITEM NO</th>
				<th width="25%">E/D</th>
				<th width="15%">QTY</th>
				<th width="5%">DEL</th>
			</tr>
		</thead>
		<tbody id="EDPosition">
		</tbody>
	</table><br /><br />
	<span class="bar_bl">CONFIRM INFORMATION</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">ARRIVAL DATE</th>
			<td width="40%"><input type="text" name="_arrived_date" class="reqd" size="15" value="<?php echo date("j-M-Y") ?>"></td>
			<th width="15%">CHECKED BY</th>
			<td><input type="text" name="_checked_by" class="req" maxlength="32" value="<?php echo $S->getValue("ma_account") ?>"></td>
		</tr>
		<tr>
			<th>WAREHOUSE</th>
			<td colspan="3">
<?php 
$wh = array($cboFilter[3]['warehouse'][ZKP_FUNCTION], count($cboFilter[3]['warehouse'][ZKP_FUNCTION]));
for($i=0; $i<$wh[1]; $i++) {
	$v = ($i==0)?' checked':'';
	echo "\t\t\t<input type=\"radio\" name=\"_warehouse_name\" value=\"".$wh[0][$i][0]."\" id=\"".$wh[0][$i][1]."\" onclick=\"searchByCat()\"".$v."><label for=\"".$wh[0][$i][1]."\"> ".$wh[0][$i][1]." </label>\n";
}
?>
			</td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="3"><?php echo $col[0]["pl_remark"] ?></textarea></td>
		</tr>
	</table><br />
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="center">
			<button name='btnConfirm' class='input_btn' style='width:130px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm PL</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnConfirm.onclick = function() {
		var e			= window.document.frmInsert.elements;
		var countI		= window.rowPosition.rows.length;
		var countII		= window.EDPosition.rows.length;
		var numInput	= 8;
		var numInputII	= 4;
		var idx_code	= 5;				/////
		var idx_item	= idx_code+1;
		var idx_ed		= idx_code+2;
		var idx_qty		= idx_code+6;
		var idx_codeII	= idx_code+(numInput*countI)+4;
		var idx_qtyII	= idx_code+(numInput*countI)+7;

		//checking E/D
		for (var i=0; i<countI; i++) {	
			if(e(idx_ed+i*numInput).value == 'true') {
				var istrue	= false;
				var code	= trim(e(idx_code+i*numInput).value);
				var item	= e(idx_item+i*numInput).value;
				var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

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

				if(countII<=0 && qty > 0) { alert("Please complete data for outgoing Expired Date");return;}

				if(temp_qty != qty) {
					alert(
						"Check incoming expired date for:\n\n" +
						"Code : "+ code +"\nItem  : "+ item + "\n" +
						"Current arrived PL qty    : "+addcomma(qty)+"\n" +
						"Current inputed E/D qty : "+addcomma(temp_qty));
					return;
				}
	 		}
		}

		if(oForm.totalArrrived.value <= 0) {
			alert("Total arrived item have to more than 0")
			return;
		}

		if(verify(oForm)){
			if(confirm("Are you sure to confirm incoming item?")) {
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/summary_outstanding_by_supplier.php" ?>';
	}

</script>
<!--END Button-->
<!------------------------------------------ START PRINT INCOMING PL ------------------------------------------>
<?php if($numRow > 0) { ?>
<table width="80%" class="table_sub">
    <tr>
        <th height="50" valign="top" align="left"><img src="../../_images/icon/package.gif"> <strong>PACKING LIST HISTORY</strong></th>
    </tr>
</table><br />
<table width="80%" class="table_nn">
	<tr height="30px">
		<th width="15%">ARRIVAL DATE</th>
		<th width="25%">MODEL NO</th>
		<th>DESC</th>
		<th width="10%">QTY</th>
	</tr>
<?php
//INCOMING ITEM
foreach($group0 as $total1 => $group1) {
	$rowSpan = 0;
	$rowSpan += count($group1)+2;

	print "<tr>\n";
	cell_link('<b>'.$rd[$rdIdx][1].'</b>', ' valign="top" align="center" rowspan="'.$rowSpan.'"', ' href="'.$rd[$rdIdx][8].'"');	//arrival date

	$total = 0;
	$print_tr_1 = 0;
	//ORDER
	foreach($group1 as $total2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[". trim($rd[$rdIdx][3]) ."] ".$rd[$rdIdx][4]);	//model name
		cell($rd[$rdIdx][5]);					//desc
		cell(number_format($rd[$rdIdx][6]),' align="right"');	//qty
		print "</tr>\n";

		$total += $rd[$rdIdx][6]; 
		$inpl_idx	= substr($rd[$rdIdx][0],2); 
		$pl_has_ed	= $rd[$rdIdx][7]; 
		$rdIdx++;
	}
	print "<tr>\n";
	cell("ARRIVAL TOTAL", ' colspan="2" align="right" style="color:darkblue;"');
	cell(number_format($total), ' align="right" style="color:darkblue;"');
	print "</tr>\n";
	$g_total += $total;

	//print E/D of this incoming PL
	if($pl_has_ed == 't') {
		print "<tr>\n";
		print "\t<td colspan=\"2\">\n";
		include "generate_list_ed.php";
		print "\t</td>\n";
		print "</tr>\n";
	} else {
		print "<tr>\n";
		cell('');
		print "</tr>\n";
	}
}
print "<tr>\n";
cell("<b>TOTAL INCOMING</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
cell('<b>'.number_format($g_total).'</b>', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
?>
</table><br /><br />
<?php } ?><br /><br />
<!------------------------------------------ END PRINT INCOMING PL ------------------------------------------>
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