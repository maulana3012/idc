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
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_pl_by_supplier.php");
} else {
	$_code = urldecode($_GET['_code']);
}
$_idx_pl = 'claim';

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_packing_list.php";

//-------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT * FROM ".ZKP_SQL."_tb_claim WHERE cl_idx = $_code";
$result = query($sql);
$column = fetchRowAssoc($result);

if (numQueryRows($result) <= 0) {
	goPage('summary_outstanding_by_supplier.php');
}

$sql_item = "
SELECT 
 it.icat_midx, 		--0
 it.it_code,		--1
 it.it_ed,			--2
 substr(it.it_model_no,1,15) AS plit_item,			--3
 substr(it.it_desc,1,28) || '...' AS plit_desc,		--4
 clit.clit_qty,			--5
 clit.clit_remark,		--6
 clit.clit_attribute,	--7
 clit.clit_qty - ".ZKP_SQL."_arrivedQty(2,cl.cl_idx::varchar, clit.it_code) AS remain_qty --8
FROM ".ZKP_SQL."_tb_claim AS cl JOIN ".ZKP_SQL."_tb_claim_item AS clit USING(cl_idx) JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE cl.cl_idx = $_code
ORDER BY clit.it_code";
$res_item	=& query($sql_item);

//Incoming PL
$sql_pl =
"SELECT
  a.incl_idx,
  to_char(a.incl_checked_date,'dd-Mon-YY') AS checked_date,
  a.incl_inv_no,
  c.it_code,
  c.it_model_no,
  c.it_desc,
  b.init_qty,
  CASE
	WHEN (select DISTINCT(incl_idx) FROM ".ZKP_SQL."_tb_expired_claim WHERE incl_idx = a.incl_idx) is not null THEN true
	else false
  END AS incl_has_ed
FROM
  ".ZKP_SQL."_tb_in_claim AS a
  JOIN ".ZKP_SQL."_tb_in_claim_item AS b USING(incl_idx)
  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE a.cl_idx = $_code
ORDER BY a.incl_idx, a.incl_checked_date, c.it_code";
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res_pl = query($sql_pl);
$numRow = numQueryRows($res_pl);

while($col =& fetchRowAssoc($res_pl)) {

	$rd[] = array(
		$col['incl_idx'],		//0
		$col['checked_date'],	//1
		$col['incl_inv_no'],	//2
		$col['it_code'], 		//3
		$col['it_model_no'],	//4
		$col['it_desc'],		//5
		$col['init_qty'], 		//6
		$col['incl_has_ed'] 	//7
	);

	//1st grouping
	if($cache[0] != $col['incl_idx']) {
		$cache[0] = $col['incl_idx'];
		$group0[$col['incl_idx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['incl_idx']][$col['it_code']] = 1;
}
$g_total = 0;
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;
	var idx_qty		= 16;					/////
	var idx_in_wh	= idx_qty + 1;
	var idx_arrived	= idx_qty + 2;
	var idx_on_deli	= idx_qty + 3;

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

function checkQty(value, i, part){
	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;
	var idx_qty		= 16;						/////
	var idx_in_wh	= idx_qty+1;
	var idx_arrived	= idx_qty+2;
	var idx_on_deli	= idx_qty+3;
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
				oTD[i].innerText	= numFormatval(f2.elements[3].value+'',2);
				oTD[i].align		= "right";
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_ed_it_qty[]";
				oTextbox[i].value	= f2.elements[3].value;
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
<small class="comment">* Source by replace claim</small>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value="confirm_claim">
<input type="hidden" name="_code" value="<?php echo $column['cl_idx']?>">
<input type="hidden" name="_po_code" value="<?php echo '' ?>">
<input type="hidden" name="_sp_code" value="<?php echo $column['cl_sp_code']?>">
<input type="hidden" name="_pl_type" value="<?php echo $column['cl_type']?>">
<input type="hidden" name="_invoice_no" value="<?php echo $column['cl_inv_no']?>">
<input type="hidden" name="_shipment_mode" value="<?php echo $column['cl_shipment_mode']?>">
	<table width="100%" class="table_box">
		<tr>
			<td colspan="4"><span class="bar_bl">PL INFORMATION</span></td>
		</tr>
		<tr>
			<th width="15%">INVOICE NO</th>
			<td width="40%"><span class="bar"><?php echo $column["cl_inv_no"] ?></span></td>
			<th width="15%">INVOICE DATE</th>
			<td><?php echo date('j-M-Y', strtotime($column['cl_inv_date']))?></td>
		</tr>
		<tr>
			<th>ETD DATE</th>
			<td><?php echo date('j-M-Y', strtotime($column['cl_etd_date'])) ?></td>
			<th>ETA DATE</th>
			<td><?php echo date('j-M-Y', strtotime($column['cl_eta_date'])) ?></td>
		</tr>
		<tr>
			<th>RECEIVED BY</th>
			<td><?php echo $column["cl_received_by"]?></td>
		</tr>
		<tr>
			<th>SUPPLIER NAME</th>
			<td><?php echo $column['cl_sp_name']?></td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_mode" value="sea" <?php echo (trim($column["cl_shipment_mode"]) == 'sea') ? 'checked' : '' ?> disabled>SEA &nbsp;
				<input type="radio" name="_mode" value="air" <?php echo (trim($column["cl_shipment_mode"]) == 'air') ? 'checked' : '' ?> disabled>AIR &nbsp;
				<input type="radio" name="_mode" value="other" <?php echo (trim($column["cl_shipment_mode"]) == 'other') ? 'checked' : '' ?> disabled>OTHER
				<input type="text" name="_mode_desc" class="fmt" size="10" maxlength="15" value="<?php echo $column["cl_shipment_desc"] ?>" readonly>
			</td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
	</table><br />
	<span class="bar_bl">CONFIRM ARRIVAL ITEMS</span>
	<table width="100%" class="table_box" cellspacing="1">
		<thead>
			<tr>
				<th rowspan="2" width="5%">CODE</th>
				<th rowspan="2" width="17%">ITEM</th>
				<th rowspan="2">DESC</th>
				<th rowspan="2" width="8%">ATT</th>
				<th rowspan="2" width="11%">REMARK</th>
				<th rowspan="2" width="10%">QTY</th>
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
while($items =& fetchRow($res_item)) {
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
				<td><input type="text" name="_plit_att[]" value="<?php echo trim($items[7])?>" style="width:100%" class="fmt" readonly></td>
				<td><input type="text" name="_plit_remark[]" value="<?php echo $items[6]?>" style="width:100%" class="fmt" readonly></td>
				<td><input type="text" name="_plit_qty[]" value="<?php echo number_format($items[5],2)?>" style="width:100%" class="fmtn" readonly></td>
				<td></td>
				<td><input type="text" name="_plit_in_wh[]" value="<?php echo number_format($items[5]-$items[8],2)?>" style="width:100%" class="reqn" readonly></td>
				<td><input type="text" name="_plit_arrived[]" value="<?php echo number_format($items[8],2)?>" style="width:100%" class="reqn" onKeyUp="formatNumber(this,'dot')" onBlur="checkQty(<?php echo $items[8].",".$i.",1" ?>)"></td>
				<td><input type="text" name="_plit_on_deli[]" value="0" style="width:100%" class="reqn" onKeyUp="formatNumber(this,'dot')" onBlur="checkQty(<?php echo $items[8].",".$i.",2" ?>)" readonly></td>
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
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="3"><?php echo $column["cl_remark"] ?></textarea></td>
		</tr>
	</table><br />
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="center">
			<button name='btnConfirm' class='input_btn' style='width:130px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm Claim</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnConfirm.onclick = function() {
		var e			= window.document.frmInsert.elements;
		var countI		= window.rowPosition.rows.length;
		var countII		= window.EDPosition.rows.length;
		var numInput	= 9;
		var numInputII	= 4;
		var idx_code	= 11;				/////
		var idx_item	= idx_code+1;
		var idx_ed		= idx_code+2;
		var idx_qty		= idx_code+7;
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
	cell_link('<b>'.$rd[$rdIdx][1].'</b>', ' valign="top" align="center" rowspan="'.$rowSpan.'"',
		' href="detail_confirm_claim.php?_code='.$_code.'&_incl_idx='.$rd[$rdIdx][0].'"');	//arrival date

	$total 		= 0;
	$print_tr_1 = 0;
	//ORDER
	foreach($group1 as $total2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[". trim($rd[$rdIdx][3]) ."] ".$rd[$rdIdx][4]);	//model name
		cell($rd[$rdIdx][5]);									//desc
		cell(number_format($rd[$rdIdx][6]),' align="right"');	//qty
		print "</tr>\n";

		$total += $rd[$rdIdx][6]; 
		$inpl_idx	= $rd[$rdIdx][0]; 
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