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
ckperm(ZKP_SELECT, HTTP_DIR . $currentDept . "/billing/index.php");

//Check PARAMETER
if(!isset($_GET['_code']) || $_GET['_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code = urldecode($_GET['_code']);

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_code'";
if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");

$column =& fetchRowAssoc($result);
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
//Calculate (It different with revise_order.php & input_order.php)
function reCalculationTotal() {
	//set Total EA & Amount
	var f = window.document.all;
	var count = window.rowPosition.rows.length;
	var sumOfQty = 0;
	var sumOfTotal = 0;
	
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseInt(oRow.cells(4).innerText);
		sumOfTotal = sumOfTotal + parseFloat(removecomma(oRow.cells(5).innerText));
	}

	var totalAfterDisc = sumOfTotal;
	var total_disc	  = 0;
	var vat			  = 0;
	var delivery_cost = 0;

	if(f._disc.value > 0) {
		total_disc = Math.round(sumOfTotal * f._disc.value/100);
		totalAfterDisc = sumOfTotal - total_disc;
	}

	if (f._vat.value != '') {
		vat = f._vat.value;
	}

	if (f._delivery_freight_charge.value != '') {
		delivery_cost = parseFloat(removecomma(f._delivery_freight_charge.value));
	}

	vat = Math.round(parseFloat(vat) / 100 * totalAfterDisc);
	var totalAmount	= totalAfterDisc + vat + delivery_cost;

	var vat = 10 / 100 * sumOfTotal;

	f.totalQty.value	  = addcomma(sumOfQty);
	f.total.value		  = numFormatval(sumOfTotal.toString(), 0);
	f.total2.value		  = numFormatval(totalAfterDisc.toString(), 0);
	f.totalVat.value	  = numFormatval(vat.toString(), 0);
	f.totalDelivery.value = numFormatval(delivery_cost + '', 0);
	f.totalDisc.value	  = numFormatval(total_disc + '', 0);
	f.totalAmount.value   = numFormatval(totalAmount + '', 0);
}
</script>
</head>
<body style="margin:8" topmargin="0" leftmargin="0" onLoad="reCalculationTotal()">
<span class="font-size=18px;"><b>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] DETAIL INVOICE : <?php echo $_code ?></b></span><br />
<?php
if($column["bill_type_invoice"]==0) echo "<span class=\"comment\"><i>* issue invoice &amp; outgoing item</i></span>";
else if($column["bill_type_invoice"]==1) echo "<span class=\"comment\"><i>* issue invoice only</i></span>";
?>
<br /><br />
<?php
$numRow = numQueryRows($result);
if ($numRow == 0) {
	echo "<span class=\"comment\">Invoice no <b>$_code</b> doesn't exist in system. So, you can't see the detail invoice.</span><br /><br />\n";
	echo "<button name=\"btnCLoseWindow\" class=\"input_sky\" onCLick=\"window.close()\">CLOSE</close>";
	exit;
}
?>
<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><strong class="info">BILLING INFORMATION</strong></td>
		<td colspan="2" align="right">
			<I>Last updated by : <?php echo $column['bill_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['bill_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th width="18%">CODE</th>
		<td colspan="2"><b><?php echo $_code ?></b></td>
		<th width="18%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['bill_inv_date']))?></td>
	</tr>
	<tr>
		<th>PAJAK NO.</th>
		<td colspan="2"><?php echo $column['bill_vat_inv_no'] ?></td>
		<th>RECEIVED BY</th>
		<td><?php echo $column['bill_received_by']?></td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td colspan="2"><?php echo $column['bill_do_no']?></td>
		<th>DO DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['bill_do_date']))?></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td colspan="2"><?php echo $column['bill_po_no']?></td>
		<th>PO DATE</th>
		<td><?php echo ($column['bill_po_date'] != '') ? date("j-M-Y", strtotime($column['bill_po_date'])) : ''?></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td colspan="2">
			<input type="checkbox" disabled>
			<input type="hidden" name="_sj_code" class="req" value="<?php echo $column['bill_sj_code'] ?>">
			<?php echo $column['bill_sj_code']?>
		</td>
		<th width="15%">SJ DATE</th>
		<td><input type="hidden" name="_sj_date" value="<?php echo $column['bill_sj_date']?>"><?php echo date("j-M-Y", strtotime($column['bill_sj_date']))?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td colspan="2">
			<input type="radio" name="_btnVat" value="y" disabled <?php echo ($column['bill_vat'] > 0) ? 'checked' : '' ?>><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['bill_vat'] ?>" readonly>%
			<input type="radio" name="_btnVat" value="n" disabled <?php echo ($column['bill_vat'] > 0) ? '' : 'checked' ?>>NON VAT
		</td>
		<th>TYPE OF PAJAK</th>
		<td>
			<input type="radio" name="_type_of_pajak" value="IO" <?php echo ($column['bill_type_pajak'] == 'IO') ? "checked" : '' ?> disabled>IO &nbsp;
			<input type="radio" name="_type_of_pajak" value="IP" <?php echo ($column['bill_type_pajak'] == 'IP') ? "checked" : '' ?> disabled>IP
		</td>
	</tr>
	<tr>
		<th rowspan="4">CUSTOMER</th>
		<th width="12%">CODE</th>
		<td width="10%"><?php echo $column['bill_cus_to'] ?></td>
		<th>NAME</th>
		<td><?php echo $column['bill_cus_to_name'] ?></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td colspan="3"><?php echo $column['bill_cus_to_attn'] ?></td>
	</tr>
	<tr>
		<th>NPWP</th>
		<td colspan="3"><?php echo $column['bill_npwp'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo cut_string($column['bill_cus_to_address'],60) ?></td>
	</tr>
	<tr>
		<th rowspan="2">FAKTUR<br />PAJAK TO</th>
		<th width="12%">CODE</th>
		<td><?php echo $column['bill_pajak_to'] ?></td>
		<th>NAME</th>
		<td colspan="3"><?php echo $column['bill_pajak_to_name'] ?></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><?php echo cut_string($column['bill_pajak_to_address'],60) ?></td>
	</tr>
</table><br />
	<strong>ITEM LIST</strong>
	<table width="100%" class="table_box">
		<thead>
			<tr>
				<th width="5%">CODE</th>
				<th width="13%">ITEM NO</th>
				<th width="28%">DESCRIPTION</th>
				<th width="12%">UNIT PRICE</th>
				<th width="5%">QTY</th>
				<th width="12%">AMOUNT(Rp)</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
$sql = "SELECT 
icat_midx, 		--0
it_code,		--1
it_type,		--2
it_model_no,	--3
it_desc,		--4
biit_unit_price,	--5
biit_qty,		--6
biit_unit_price * biit_qty AS amount,	--7
biit_remark		--8
FROM ".ZKP_SQL."_tb_billing_item
WHERE bill_code = '$_code'
ORDER BY it_code";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
			<tr>
				<td><?php echo $items[1]?></td>
				<td><?php echo cut_string($items[3],10)?></td>
				<td><?php echo cut_string($items[4],30)?></td>
				<td align="right"><?php echo number_format((double)$items[5])?></td>
				<td align="right"><?php echo $items[6]?></td>
				<td align="right"><?php echo number_format((double)$items[7])?></td>
			</tr>
<?php
} //END WHILE
?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">SUB TOTAL</th>
			<th width="5%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="15%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th align="right">DISC %</th>
			<th width="5%"><input name="_disc" type="text" class="reqn" style="width:100%" value="<?php echo $column['bill_discount'] ?>" onBlur="updateAmount()"></th>
			<th width="15%"><input name="totalDisc" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">Before Vat</th>
			<th width="12%"><input name="total2" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">VAT</th>
			<th width="15%"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">Delivery Cost</th>
			<th width="15%"><input name="totalDelivery" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">GRAND TOTAL</th>
			<th width="15%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
	</table><br>
	<strong>CONDITION</strong>
	<table class="table_box" width="100%">
		<tr>
			<th width="18%" valign="top" rowspan="2">DELIVERY</th>
			<td>1. &nbsp; <?php echo $column['bill_delivery_warehouse']?> &nbsp; ex W/house(P/C/D)</td>
			<td>2. &nbsp; <?php echo $column['bill_delivery_franco']?> &nbsp; Franco(P/D)</td>
		</tr>
		<tr>
			<td>by <?php echo $column['bill_delivery_by']?></td>
			<td>Freight charge: <input type="text" name="_delivery_freight_charge" size="8" value="<?php echo ($column['bill_delivery_freight_charge'] <= 0) ? '' : number_format((double)$column['bill_delivery_freight_charge'])?>" class="fmtn" disabled></td>
		</tr>
		<tr>
			<th rowspan="7" valign="top" width="12%">PAYMENT</th>
			<td colspan="2">
			 <input type="checkbox" name="_payment_chk[]" value="1" <?php echo ($column['bill_payment_chk'] & 1)? "checked":""?> disabled>COD &nbsp;
			 <input type="checkbox" name="_payment_chk[]" value="2" <?php echo ($column['bill_payment_chk'] & 2)? "checked":""?> disabled>PREPAID &nbsp;
			 <input type="checkbox" name="_payment_chk[]" value="4" <?php echo ($column['bill_payment_chk'] & 4)? "checked":""?> disabled>Consignment &nbsp;
			 <input type="checkbox" name="_payment_chk[]" value="8" <?php echo ($column['bill_payment_chk'] & 8)? "checked":""?> disabled>Free/TO/LF/RP/PT
			</td>
		</tr>
		<tr>
			<td>5. Within  &nbsp; <?php echo $column['bill_payment_widthin_days']?>  &nbsp; days after</td>
			<td>5a. <?php echo $column['bill_payment_sj_inv_fp_tender']?></td>
		</tr>
		<tr>
			<td>5b. Closing on <?php echo empty($column['bill_payment_closing_on']) ? "" : date("j-M-Y",strtotime($column['bill_payment_closing_on']))?></td>
			<td><?php echo $column['bill_payment_for_the_month_week'] ?>For the Month/Week(M/W)</td>
		</tr>
		<tr>
			<td colspan="2">1)<input type="checkbox" name="_payment_chk[]" value="16" <?php echo ($column['bill_payment_chk'] & 16)? "checked":""?> disabled>Cash by <input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" value="<?php echo $column['bill_payment_cash_by']?>" disabled></td>
		</tr>
		<tr>
			<td colspan="2">2)<input type="checkbox" name="_payment_chk[]" value="32" <?php echo ($column['bill_payment_chk'] & 32)? "checked":""?> disabled>Check by <input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" value="<?php echo $column['bill_payment_check_by']?>" disabled></td>
		</tr>
		<tr>	
			<td colspan="2">3)<input type="checkbox" name="_payment_chk[]" value="64" <?php echo ($column['bill_payment_chk'] & 64)? "checked":""?> disabled>Transfer by <input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" value="<?php echo $column['bill_payment_transfer_by']?>" disabled></td>
		</tr>
		<tr>
			<td colspan="2">
				4)<input type="checkbox" name="_payment_chk[]" value="128" <?php echo ($column['bill_payment_chk'] & 128)? "checked":""?> disabled>Giro &nbsp;
				Issue : <input type="text" name="_payment_giro_issue" size="10" class="fmtd" value="<?php echo ($column['bill_payment_giro_issue'] != '') ? date("j-M-Y", strtotime($column['bill_payment_giro_issue'])) : ''?>" disabled>
				Due : <input type="text" name="_payment_giro_due" size="10" class="reqd" value="<?php echo date("j-M-Y", strtotime($column['bill_payment_giro_due']))?>" disabled>
			</td>
		</tr>
		<tr>
			<th rowspan="2" valign="top">BANK</th>
			<td>
				<input type="radio" name="_bank" value="BCA1" onCLick=bankDesc(this) id="bca1" disabled <?php echo ($column['bill_payment_bank'] == 'BCA1') ? 'checked' : '' ?>><label for="bca1">BCA 1</label><br />
				<input type="radio" name="_bank" value="BCA2" onCLick=bankDesc(this) id="bca2" disabled <?php echo ($column['bill_payment_bank'] == 'BCA2') ? 'checked' : '' ?>><label for="bca2">BCA 2</label><br />
				<input type="radio" name="_bank" value="MANDIRI" onCLick=bankDesc(this) id="mandiri" disabled <?php echo ($column['bill_payment_bank'] == 'MANDIRI') ? 'checked' : '' ?>><label for="mandiri">Mandiri</label><br />
			</td>
			<td>
				<input type="radio" name="_bank" value="BII1" onCLick=bankDesc(this) id="bii1" disabled <?php echo ($column['bill_payment_bank'] == 'BII1') ? 'checked' : '' ?>><label for="bii1">BII 1</label><br />
				<input type="radio" name="_bank" value="BII2" onCLick=bankDesc(this) id="bii2" disabled <?php echo ($column['bill_payment_bank'] == 'BII2') ? 'checked' : '' ?>><label for="bii2">BII 2</label><br />
				<input type="radio" name="_bank" value="DANAMON" onCLick=bankDesc(this) id="danamon" disabled <?php echo ($column['bill_payment_bank'] == 'DANAMON') ? 'checked' : '' ?>><label for="danamon">Danamon</label>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea name="_bank_address" rows="3" style="width:100%" readonly><?php echo $column['bill_payment_bank_address'] ?></textarea>
			</td>
		</tr>
		<tr>
			<th>TUKAR FAKTUR<br />DATE</th>
			<td><?php echo ($column['bill_tukar_faktur_date'] != '') ? date("j-M-Y", strtotime($column['bill_tukar_faktur_date'])) : ''?></td>
		</tr>
		<tr>
			<th>SHIP TO</th>
			<td colspan="4">
				<input type="text" name="_ship_to" class="fmt" size="5" value="<?php echo $column['bill_ship_to'] ?>" disabled>
				<input type="text" name="_ship_name" class="fmt" size="50" value="<?php echo $column['bill_ship_to_name'] ?>" disabled>
			</td>
		</tr>
	</table><br />
	<strong>OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="25%">SIGN BY</th>
			<td width="25%"><?php echo $column['bill_signature_by'] ?></td>
			<th width="25%">PAPER FORMAT</th>
			<td>
				<input type="radio" name="_paper_format" value="A" id="A" <?php echo ($column['bill_paper_format']=='A')?'checked':'' ?> disabled><label for="A">A &nbsp; </label>
				<input type="radio" name="_paper_format" value="B" id="B" <?php echo ($column['bill_paper_format']=='B')?'checked':'' ?> disabled><label for="B">B </label>
			</td>
		</tr>
		<?php if($column['bill_vat'] > 0) {?>
		<tr>
			<th>SIGN PAJAK BY</th>
			<td colspan="3">
				<input type="radio" name="_signature_pajak_by" value="A"<?php echo ($column['bill_signature_pajak_by']=='A'?" checked":"") ?> disabled>In Ki Kim Lee &nbsp;
				<input type="radio" name="_signature_pajak_by" value="B"<?php echo ($column['bill_signature_pajak_by']=='B'?" checked":"") ?> disabled>Min Sang Hyun
			</td>
		</tr>
		<?php } ?>
	</table><br />
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name="btnClose" class="input_sky" onClick="window.close();">Close <img src="../../_images/icon/close_mini.gif"></button> &nbsp;
			<?php if(isset($_GET['_cus_code']) && $_GET['_cus_code'] != '') { ?>
			<button name="btnList" class="input_sky" onClick="window.history.go(-1)">LIST</button>
			<?php } ?>
		</td>
	</tr>
</table><br />
<?php
$total_paid = 0;

$pay_sql = "
SELECT
  pay_idx,
  to_char(pay_date, 'dd-Mon-yy') AS pay_date,
  pay_paid,
  pay_remark,
  pay_inputed_by,
  pay_inputed_timestamp
FROM ".ZKP_SQL."_tb_payment
WHERE bill_code = '$_code'
ORDER BY pay_date";

$pay_res	=& query($pay_sql);
if (numQueryRows($pay_res) > 0) {
?>
<strong>PAYMENT CONFIRM</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="5%">NO</th>
		<th>REMARK</th>
		<th width="25%">INPUT</th>
		<th width="15%">PAYMENT DATE</th>
		<th width="23%">AMOUNT (Rp)</th>
	</tr>
<?php
$i = 1;
while($payment =& fetchRow($pay_res)) {
?>
	<tr>
		<td align="center"><?php echo $i++ ?></td>
		<td><?php echo $payment[3] ?></td>
		<td><?php echo $payment[4] . ", " . date('j-M-Y', strtotime($payment[5])) ?></td>
		<td align="center"><?php echo $payment[1] ?></td>
		<td align="right"><?php echo number_format((double)$payment[2], 2) ?></td>
	</tr>
<?php
	$total_paid += $payment[2];
}
?>
	<tr>
		<th colspan="4" align="right">TOTAL PAID</th>
		<th align="right"><input type="text" class="fmtn" style="width:80%" value="<?php echo number_format((double)$total_paid, 2) ?>" readonly></th>
	</tr>
	<tr>
		<th colspan="4" align="right"><b style="color:red">LACK</b></th>
		<th align="right"><input type="text" class="fmtn" style="color:red;width:80%" value="<?php echo number_format((double)$column['bill_remain_amount'], 2) ?>" readonly></th>
	</tr>
	<tr>
		<th colspan="4" align="right">TOTAL AMOUNT</th>
		<th align="right"><input type="text" class="fmtn" style="width:80%" value="<?php echo number_format((double)$column['bill_total_billing'], 2) ?>" readonly></th>
	</tr>
</table><br />
<?php
}
?>
<!--END Button-->
</body>
</html>