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
$left_loc = "summary_po_by_supplier.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php");
} else {
	$_code = urldecode($_GET['_code']); 
}

//-------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT *, ".ZKP_SQL."_getPLLocalCode('$_code', null) AS pl_idx FROM ".ZKP_SQL."_tb_po_local WHERE po_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column["po_confirmed_timestamp"] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
}

$sql_item	= "
SELECT 
  it_code,
  it_model_no, 
  it_desc, 
  poit_unit, 
  poit_unit_price, 
  poit_qty,
  poit_qty*poit_unit_price AS amount, 
  poit_remark
FROM ".ZKP_SQL."_tb_po_local_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE po_code = '$_code'
ORDER BY it_code";
$res_item	= query($sql_item);

//Incoming PL
$sql_pl =
"SELECT
  a.inlc_idx,
  to_char(a.inlc_checked_date,'dd-Mon-YY') AS checked_date,
  po_code || ' #' || pl_no AS invoice_no,
  c.it_code,
  c.it_model_no,
  c.it_desc,
  b.init_qty,
  '../packing_list/detail_confirm_pl_local.php?_inlc_idx='||inlc_idx AS go_page
FROM
  ".ZKP_SQL."_tb_in_local AS a
  JOIN ".ZKP_SQL."_tb_in_local_item AS b USING(inlc_idx)
  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE po_code = '$_code'
ORDER BY a.inlc_idx, a.inlc_checked_date, c.it_code";
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res_pl = query($sql_pl);
$numRow = numQueryRows($res_pl);

while($col =& fetchRowAssoc($res_pl)) {

	$rd[] = array(
		$col['inlc_idx'],		//0
		$col['checked_date'],	//1
		$col['invoice_no'],		//2
		$col['it_code'], 		//3
		$col['it_model_no'],	//4
		$col['it_desc'],		//5
		$col['init_qty'], 		//6
		$col['go_page'] 		//7		
	);

	//1st grouping
	if($cache[0] != $col['inlc_idx']) {
		$cache[0] = $col['inlc_idx'];
		$group0[$col['inlc_idx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['inlc_idx']][$col['it_code']] = 1;
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
function reCalculationTotal() {
	var count = window.rowPosition.rows.length;
	var sumOfQty	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty	+= parseInt(removecomma(oRow.cells(5).innerText));
		sumOfTotal	+= parseFloat(removecomma(oRow.cells(6).innerText));
	}

	add1		= parseFloat(removecomma(window.document.all.totalAdd1.value));
	add2		= parseFloat(removecomma(window.document.all.totalAdd2.value));
	sumBeforeVat= sumOfTotal+add1+add2;
	sumVat		= parseFloat(window.document.all.vat.value)/100 * sumBeforeVat;
	sumOfGrand	= sumBeforeVat+sumVat;

	window.document.all.totalQty.value		= addcomma(sumOfQty);
	window.document.all.totalAmount.value	= numFormatval(sumOfTotal + '', 2);
	window.document.all.totalBeforeVat.value= numFormatval(sumBeforeVat + '', 2);
	window.document.all.totalVat.value		= numFormatval(sumVat + '', 2);
	window.document.all.totalGrand.value	= numFormatval(sumOfGrand + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="reCalculationTotal()">
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL PO</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column["po_code"] ?>">
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><span class="bar_bl">PO INFORMATION</span></td>
		<td colspan="2" align="right"><I>Last updated by : <?php echo ucfirst($column['po_lastupdated_by_account']).date(', j-M-Y g:i:s', strtotime($column['po_lastupdated_timestamp']))?></I></td>
	</tr>
	<tr>
		<th width="15%">PO NO</th>
		<td width="34%"><b><?php echo $column["po_code"] ?></b></td>
		<th width="15%">PO DATE</th>
		<td><input name="_po_date" type="text" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column["po_date"])) ?>" maxlength="64" readonly></td>
	</tr>
	<tr>
		<th width="15%">PO TYPE</th>
		<td>
			<input type="radio" name="_po_type" value="1" id="1"<?php echo ($column["po_type"]==1) ? ' checked' : '' ?> disabled><label for="1">NORMAL</label> &nbsp;
			<input type="radio" name="_po_type" value="2" id="2"<?php echo ($column["po_type"]==2) ? ' checked' : '' ?> disabled><label for="2">NON VAT</label>
		</td>
		<th>DELIVERY DATE</th>
		<td><input name="_deli_date" type="text" class="fmtd" size="15" maxlength="64" value="<?php echo ($column["po_delivery_date"]=='') ? '' : date('d-M-Y', strtotime($column["po_delivery_date"])) ?>" readonly></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="3" width="12%">SUPPLIER</th>
		<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
		<td width="25%"><input name="_sp_code" type="text" class="req" size="6" value="<?php echo $column["sp_code"] ?>" readOnly></td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_sp_name" class="req" style="width:100%" maxlength="125" value="<?php echo $column["po_sp_name"] ?>" readonly></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_sp_attn" class="fmt" size="25" maxlength="32" value="<?php echo $column["po_sp_attn"] ?>" readonly></td>
		<th>CONTACT</th>
		<td>
		Telp : <input type="text" name="_sp_phone" class="fmt" size="15" maxlength="32" value="<?php echo $column["po_sp_phone"] ?>" readonly> &nbsp;
		Fax : <input type="text" name="_sp_fax" class="fmt" size="15" maxlength="32" value="<?php echo $column["po_sp_fax"] ?>" readonly>
		</td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_sp_address" class="fmt" style="width:100%" value="<?php echo $column["po_sp_address"] ?>" readonly></td>
	</tr>
</table><br />
<span class="bar_bl">ITEM LIST</span> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="17%">ITEM</th>
			<th width="25%">DESC</th>
			<th width="5%">UNIT</th>
			<th width="10%">UNIT PRICE<br />(Rp)</th>
			<th width="8%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php while($items =& fetchRow($res_item)) { ?>
	<tr id="<?php echo $items[0]?>">
		<td><?php echo $items[0]?></td>
		<td><?php echo $items[1]?></td>
		<td><?php echo $items[2]?></td>
		<td align="center"><?php echo $items[3]?></td>
		<td align="right"><?php echo number_format($items[4]) ?></td>
		<td align="right"><?php echo number_format($items[5]) ?></td>
		<td align="right"><?php echo number_format($items[6],2) ?></td>
		<td><?php echo $items[7]?></td>
	</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">Total</th>
		<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="12%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="18%">&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Addtional charge 1 : <input name="_add_charge1" type="text" class="fmt" style="width:30%" value="<?php echo $column["po_text_charge1"] ?>"></th>
		<th><input name="totalAdd1" type="text" class="reqn" style="width:100%" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="<?php echo number_format($column["po_total_charge1"],2) ?>"></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Addtional charge 2 : <input name="_add_charge2" type="text" class="fmt" style="width:30%" value="<?php echo $column["po_text_charge2"] ?>"></th>
		<th style="border-bottom:1px solid #006da5"><input name="totalAdd2" type="text" class="reqn" style="width:100%" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="<?php echo number_format($column["po_total_charge2"],2) ?>"></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Before VAT</th>
		<th><input name="totalBeforeVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">VAT &nbsp; <input name="vat" type="text" class="reqn" style="width:30px" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="<?php echo $column["po_vat"] ?>"> %</th>
		<th style="border-bottom:1px solid #006da5"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">GRAND TOTAL</th>
		<th><input name="totalGrand" type="text" class="reqn" style="width:100%;" readonly></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right">SAYS</th>
		<th colspan="4"><input name="_says_in_word" type="text" class="req" style="width:100%" value="<?php echo $column["po_says_in_words"] ?>"></th>
		</th>
	</tr>
</table><br />
<span class="bar_bl">OTHERS</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PREPARED BY</th>
		<td><?php echo $column["po_prepared_by"] ?></td>
		<th width="15%">CONFIRMED BY</th>
		<td><?php echo $column["po_confirmed_by"] ?></td>
		<th width="15%">APPROVED BY</th>
		<td><?php echo $column["po_approved_by"] ?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="5"><textarea name="_remark" style="width:100%" rows="3" readOnly><?php echo $column["po_remark"]?></textarea></td>
	</tr>
</table>
<?php if($column['pl_idx'] != '') { ?>
<br /><br />
<table width="85%" class="table_sub">
    <tr>
        <th height="50" valign="top" align="left"><img src="../../_images/icon/package.gif"> <strong>PACKING LIST HISTORY</strong></th>
    </tr>
</table><br />
<table width="85%" class="table_nn">
	<tr height="30px">
		<th width="12%">ARRIVAL DATE</th>
		<th width="20%">INVOICE NO</th>
		<th width="25%">MODEL NO</th>
		<th>DESC</th>
		<th width="10%">QTY</th>
	</tr>
<?php
//INCOMING ITEM
foreach($group0 as $total1 => $group1) {
	$rowSpan = 0;
	$rowSpan += count($group1);

	print "<tr>\n";
	cell($rd[$rdIdx][1], ' valign="top" align="center" rowspan="'.$rowSpan.'"');	//arrival date
	cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' valign="top" align="center" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][7].'" target="_blank"');	//invoice no

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
		$rdIdx++;
	}
	print "<tr>\n";
	cell("ARRIVAL TOTAL", ' colspan="4" align="right" style="color:darkblue;"');
	cell(number_format($total), ' align="right" style="color:darkblue;"');
	print "</tr>\n";
	$g_total += $total;
}
print "<tr>\n";
cell("<b>TOTAL INCOMING</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
?>
</table><br />
<?php } ?>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['po_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
</table><br /><br />
<script language="javascript" type="text/javascript">

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/warehouse/pdf/download_po_pdf.php?_code=<?php echo $_code ?>&_po_date=<?php echo $column['po_date'] ?>&_rev=" + window.document.all._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php" ?>';
	}
</script>
<!---------------------------------------- start print lock document ---------------------------------------->
<?php if($column['pl_idx'] != '') { ?>
<table width="100%" class="table_box">
	<tr>
		<td width="10%" valign="top"><img src="../../_images/icon/warning.gif"></td>
		<td>
			<span>
			This is a locked document. To modify document, see the hierarchy process.<br /><br />
			Here are the possibility(es) :
			</span>
			<ul>
				<li> This PO already already confirmed.</li>
			</ul>
		</td>
	</tr>
</table>
<?php } ?>
<!---------------------------------------- end print lock document ---------------------------------------->
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