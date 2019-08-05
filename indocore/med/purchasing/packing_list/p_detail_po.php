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
ckperm(ZKP_SELECT, HTTP_DIR . $currentDept . "/packing_list/index.php");

//Check PARAMETER
if(!isset($_GET['_code']) || $_GET['_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code		= urldecode($_GET['_code']);
$_sp_code	= urldecode($_GET['_sp_code']);

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_po WHERE po_code = '$_code'";
if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");

$column =& fetchRowAssoc($result);
?>
<html>
<head>
<title>DETAIL PO</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
//Calculate (It different with revise_order.php & input_order.php)
function reCalculationTotal() {
	//set Total EA & Amount
	var f = window.document.all;
	var count = window.rowPosition.rows.length;
	var sumOfQty = 0;
	var sumOfTotal = 0;
	
	for (var i=0; i<count; i++) {
		var oRow	= window.rowPosition.rows(i);
		sumOfQty	= sumOfQty + parseInt(oRow.cells(4).innerText);
		sumOfTotal	= sumOfTotal + parseFloat(removecomma(oRow.cells(5).innerText));
	}

	f.totalQty.value	 = addcomma(sumOfQty);
	f.totalAmount.value	 = numFormatval(sumOfTotal + '', 2);
}
</script>
</head>
<body style="margin:8" topmargin="0" leftmargin="0" onLoad="reCalculationTotal()">
<h4>DETAIL PO <?php echo $_code ?></h4>
<?php
$numRow = numQueryRows($result);
if ($numRow == 0) {
	echo "<span class=\"comment\">PO no <b>$_code</b> doesn't exist in system. So, you can't see the detail PO.</span><br /><br />\n";
	echo "<button name=\"btnCLoseWindow\" class=\"input_sky\" onCLick=\"window.close()\">CLOSE</close>";
	exit;
}
?>
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><strong>PO INFORMATION</strong></td>
		<td colspan="2" align="right">
			<I>Last updated by : <?php echo $column['po_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['po_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th width="15%">PO NO</th>
		<td><?php echo $column['po_code'] ?></td>
		<th width="15%">PO DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column['po_date'])) ?></td>
	</tr>
	<tr>
		<th width="15%">SHIPMENT TYPE</th>
		<td>
			<input type="radio" name="_type" value="1" disabled <?php echo ($column['po_type'] == 1) ? "checked" : "" ?>>NORMAL <br />
			<input type="radio" name="_type" value="2" disabled <?php echo ($column['po_type'] == 2) ? "checked" : "" ?>>DOOR TO DOOR
		</td>
		<th>SHIPMENT MODE</th>
		<td>
			<input type="radio" name="_shipment_mode" value="sea" <?php echo (trim($column['po_shipment_mode']) == 'sea') ? "checked" : "" ?> disabled>SEA &nbsp;
			<input type="radio" name="_shipment_mode" value="air" <?php echo (trim($column['po_shipment_mode']) == 'air') ? "checked" : "" ?> disabled>AIR &nbsp;
			<input type="radio" name="_shipment_mode" value="other" <?php echo (trim($column['po_shipment_mode']) == 'other') ? "checked" : "" ?> disabled>OTHER
		</td>
	</tr>
	<tr>
		<th width="15%">RECEIVED BY</th>
		<td width="34%"><?php echo $column['po_received_by']?></td>
		<th>LAYOUT TYPE</th>
		<td>
			<input type="radio" name="_layout_type" value="1" <?php echo ($column['po_layout_type'] == 1) ? "checked" : "" ?> disabled>1 &nbsp; 
			<input type="radio" name="_layout_type" value="2" <?php echo ($column['po_layout_type'] == 2) ? "checked" : "" ?> disabled>2 &nbsp;
			<input type="radio" name="_layout_type" value="3" <?php echo ($column['po_layout_type'] == 3) ? "checked" : "" ?> disabled>3 &nbsp;
			<input type="radio" name="_layout_type" value="4" <?php echo ($column['po_layout_type'] == 4) ? "checked" : "" ?> disabled>4
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="12%">SUPPLIER</th>
		<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
		<td width="25%"><?php echo $column['po_sp_code']?></td>
		<th width="15%">NAME</th>
		<td width="43%"><?php echo $column['po_sp_name']?></td>
	</tr>
</table><br />
<strong>ITEM LIST</strong>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="15%">ITEM</th>
			<th width="25%">DESC</th>
			<th width="12%">PRICE<br />(U$)</th>
			<th width="10%">QTY</th>
			<th width="13%">AMOUNT<br />(U$)</th>
			<th width="10%">REMARK</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php
$sql = "
SELECT 
  pi.icat_midx, 		--0
  pi.it_code,			--1
  pi.poit_item,			--2
  pi.poit_desc,			--3
  pi.poit_unit_price,	--4
  pi.poit_qty,			--5
  CASE
  	WHEN po.po_layout_type = 3 THEN pi.poit_unit_price * pi.poit_qty/100
  	ELSE pi.poit_unit_price * pi.poit_qty
  end AS amount,	--6
  pi.poit_remark		--7
FROM ".ZKP_SQL."_tb_po AS po JOIN ".ZKP_SQL."_tb_po_item AS pi USING(po_code)
WHERE po.po_code = '$_code'
ORDER BY pi.it_code";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
		<tr id="<?php echo trim($items[1])?>">
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td><?php echo substr($items[3],0,20) ?></td>
			<td align="right"><?php echo number_format($items[4],2)?></td>
			<td align="right"><?php echo $items[5]?></td>
			<td align="right"><?php echo number_format($items[6],2)?></td>
			<td><?php echo $items[7]?></td>
		</tr>
<?php
} //END WHILE
?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">GRAND TOTAL</th>
		<th width="10%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="15%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="10%">&nbsp;</th>
	</tr>
</table><br>
<strong>OTHERS</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="20%">PREPARED BY</th>
		<td><?php echo $column['po_prepared_by']?></td>
		<th width="20%">CONFIRMED BY</th>
		<td><?php echo $column['po_confirmed_by']?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="3" readonly><?php echo $column['po_remark']?></textarea></td>
	</tr>
	<tr>
		<th>PO PRINT<br />REMARK</th>
		<td colspan="3"><textarea name="_print_remark" style="width:100%" rows="4" readonly><?php echo $column['po_doc_remark']?></textarea></td>
	</tr>
</table><br/>
<!--END Button-->
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name="btnClose" class="input_sky" onClick="window.close();">CLOSE</button> &nbsp;
			<?php if(isset($_GET['_sp_code']) && $_GET['_sp_code'] != '') { ?>
			<button name="btnList" class="input_sky" onClick="window.history.go(-1)">LIST</button>
			<?php } ?>
		</td>
	</tr>
</table>
<!--END Button-->
</body>
</html>