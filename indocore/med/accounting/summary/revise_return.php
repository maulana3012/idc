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
$left_loc = 'daily_billing_by_apotik.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//=================================================================================== DEFAULT PROCESS
if($S->getValue("ma_authority") & 1)	{ $page_permission = false;}
else 									{ $page_permission = true;}

$sql = 
"SELECT *,
  isDepositUsed(turn_return_condition,turn_code,turn_cus_to,turn_return_date) AS deposit_used,
  (SELECT std_idx FROM ".ZKP_SQL."_tb_outstanding WHERE std_doc_ref='$_code' AND std_doc_type = 1) AS std_idx,
  (SELECT inc_idx FROM ".ZKP_SQL."_tb_incoming WHERE inc_doc_ref='$_code' AND inc_doc_type = 1) AS inc_idx,
  isHideFile(null,1,turn_code) AS turn_accessible,
  (select to_char(bill_inv_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_date,
  (select bill_do_no from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_do_no,
  (select to_char(bill_do_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_do_date,
  (select bill_sj_code from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_sj_code,
  (select to_char(bill_sj_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_sj_date,
  (select bill_po_no from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_po_no,
  (select to_char(bill_po_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_po_date
FROM ".ZKP_SQL."_tb_return as t WHERE turn_code = '$_code'";

$result =& query($sql);
$column =& fetchRowAssoc($result);

if($column['turn_accessible'] == 't') {
	if($page_permission) {
		$result = new ZKError(
					"NOT_ACCESSIBLE_INVOICE",
					"NOT_ACCESSIBLE_INVOICE",
					"Invoice No. <b>$_code</b> is not accessible. Please contact the manager to see the detail.");
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/_summary/daily_billing_by_group.php?cboFilterDoc=R");
	}
}

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['turn_cfm_wh_delivery_by_account'] != '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_code=".urlencode($column['turn_code']));
} else if($column["turn_paper"]==1) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return_2.php?_code=".urlencode($_code));
}

//take discount percentage from customer group
$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column['turn_cus_to']."')";
isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/index.php") : false;
$disc = fetchRow($res);

//[WAREHOUSE] billing item
$whitem_sql = "
SELECT
  a.it_code,
  b.istd_it_code_for,
  a.it_model_no,
  a.it_desc,
  b.istd_qty,
  b.istd_function,
  b.istd_remark
FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE std_idx = {$column['std_idx']}
ORDER BY it_code,istd_idx";
$whitem_res	=& query($whitem_sql);

//[CUSTOMER] billing item
$cusitem_sql = "
SELECT
 a.it_code,			--0
 a.it_model_no,		--1
 a.it_desc,			--2
 b.reit_unit_price,	--3
 b.reit_qty,		--4
 b.reit_unit_price * b.reit_qty AS amount,	--5	
 b.reit_remark,		--6
 b.reit_idx,		--7		
 b.icat_midx		--8
FROM ".ZKP_SQL."_tb_return_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE turn_code = '$_code'
ORDER BY it_code, reit_idx";
$cusitem_res	=& query($cusitem_sql);
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
function seeDetailInvRef() {

	var x = (screen.availWidth - 470) / 2;
	var y = (screen.availHeight - 600) / 2;
	var bill_code = '<?php echo $column['turn_bill_code']?>';

	var win = window.open(
		'./p_detail_billing.php?_code='+ bill_code,
		'',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Delete Item rows collection
function deleteWHItem(idx) {

	var count = window.itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.itemWHPosition.rows(i);
		if (oRow.id == idx) {
			var code_ref = trim(oRow.cells(1).innerText);
			var n = window.itemWHPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	deleteCusItem(code_ref);
	updateAmount();
}

function deleteCusItem(idx) {

	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemCusPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	updateAmount();
}

//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countWH		= window.itemWHPosition.rows.length;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputWH	= 7;
	var numInputCus	= 8;

	var idx_qty1	= 39;		/////
	var idx_qty2	= idx_qty1+(numInputWH*countWH)+1;
	var idx_price	= idx_qty1+(numInputWH*countWH);
	var idx_amount	= idx_qty1+(numInputWH*countWH)+2;
	var sumOfQty1	= 0;
	var sumOfQty2	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<countWH; i++) {
		var qty 	= parseFloat(removecomma(e(idx_qty1+i*numInputWH).value));
		sumOfQty1	+= qty;
	}

	for (var i=0; i<countCus; i++) {
		var price = parseFloat(removecomma(e(idx_price+i*numInputCus).value));
		var qty	  = parseFloat(removecomma(e(idx_qty2+i*numInputCus).value));

		e(idx_amount+i*numInputCus).value = numFormatval((price*qty)+'',0);

		sumOfQty2	+= qty;
		sumOfTotal	+= price*qty;
	}

	var totalAfterDisc = sumOfTotal;
	var total_disc	  = 0;
	var vat			  = 0;
	var delivery_cost = 0;

	if(f._disc.value > 0) {
		total_disc = Math.round(sumOfTotal * f._disc.value/100);
		totalAfterDisc = sumOfTotal - total_disc;
	}

	if (f._vat_value.value != '') {
		vat = f._vat_value.value;
	}

	if (f._delivery_freight_charge.value != '') {
		delivery_cost = parseFloat(removecomma(f._delivery_freight_charge.value));
	}

	vat = Math.round(parseFloat(vat) / 100 * totalAfterDisc);
	var totalAmount	= totalAfterDisc + vat + delivery_cost;

	f.totalWhQty.value	  = numFormatval(sumOfQty1 + '', 2);
	f.totalQty.value	  = addcomma(sumOfQty2);
	f.total.value		  = numFormatval(sumOfTotal.toString(), 0);
	f.total2.value		  = numFormatval(totalAfterDisc.toString(), 0);
	f.totalVat.value	  = numFormatval(vat.toString(), 0);
	f.totalDelivery.value = numFormatval(delivery_cost + '', 0);
	f.totalDisc.value	  = numFormatval(total_disc + '', 0);
	f.totalAmount.value   = numFormatval(totalAmount + '', 0);
}

function enabledText(o, value) {
	var f = window.document.frmInsert;

	if (value == 'cash') {
		if(o.checked == true) {
			f._payment_cash_by.disabled = false;
			f._payment_cash_by.focus();
		} else {
			f._payment_cash_by.disabled = true;
			f._payment_cash_by.value = '';
		}
	}  else if (value == 'check') {
		if(o.checked == true) {
			f._payment_check_by.disabled = false;
			f._payment_check_by.focus();
		} else {
			f._payment_check_by.disabled = true;
			f._payment_check_by.value = '';
		}
		enabledBankOption(o);
	}  else if (value == 'transfer') {
		if(o.checked == true) {
			f._payment_transfer_by.disabled = false;
			f._payment_transfer_by.focus();
		} else {
			f._payment_transfer_by.disabled = true;
			f._payment_transfer_by.value = '';
		}
		enabledBankOption(o);
	}
}

function enabledBankOption(o) {
	var f = window.document.frmInsert;

	if (o.checked == true) {
		f._bank[0].disabled = false;
		f._bank[1].disabled = false;
		f._bank[2].disabled = false;
		f._bank[3].disabled = false;
		f._bank[4].disabled = false;
		f._bank[5].disabled = false;
	} else if (o.checked == false) {
		f._bank[0].disabled   = true;
		f._bank[1].disabled   = true;
		f._bank[2].disabled   = true;
		f._bank[3].disabled   = true;
		f._bank[4].disabled   = true;
		f._bank[5].disabled   = true;
		f._bank[0].checked	  = false;
		f._bank[1].checked	  = false;
		f._bank[2].checked	  = false;
		f._bank[3].checked	  = false;
		f._bank[4].checked	  = false;
		f._bank[5].checked	  = false;
		f._bank_address.value = '';
	}
}

function bankDesc(o) {
	var f = window.document.frmInsert;

	if (o.value == "BCA1") {
		f._bank_address.value = "BCA KCU WISMA GKBI\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 0060217408";
	} else if (o.value == "BCA2") {
		f._bank_address.value = "BCA KCU KELAPA GADING\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 0650690176";
	} else if (o.value == "BII1") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 1.016.691.961";
	} else if (o.value == "BII2") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 2.016.296.083";
	} else if (o.value == "MANDIRI") {
		f._bank_address.value = "MANDIRI KCP JAKARTA DESIGN CENTER\nA/N\t: In Ki Kim\nA/C\t: 117-00-0219394-4";
	} else if (o.value == "DANAMON") {
		f._bank_address.value = "DANAMON CAB. KELAPA GADING, JAKARTA\nA/N\t: PT. Indocore Perkasa\nA/C\t: 21772660";
	} else if (o == "DANAMON") {
		f._bank_address.value = "DANAMON CAB. KELAPA GADING, JAKARTA\nA/N\t: PT. Indocore Perkasa\nA/C\t: 21772660";
	}
}

function initPage() {
<?php
if ($column['turn_payment_chk'] & 32 || $column['turn_payment_chk'] & 64 || $column['turn_payment_chk'] & 128) {
	echo "\twindow.document.frmInsert._payment_transfer_by.disabled = false;\n";
	echo "\twindow.document.frmInsert._bank[0].disabled = false;\n";
	echo "\twindow.document.frmInsert._bank[1].disabled = false;\n";
	echo "\twindow.document.frmInsert._bank[2].disabled = false;\n";
	echo "\twindow.document.frmInsert._bank[3].disabled = false;\n";
	echo "\twindow.document.frmInsert._bank[4].disabled = false;\n";
	echo "\twindow.document.frmInsert._bank[5].disabled = false;\n\n";
} else {
	echo "\twindow.document.frmInsert._payment_transfer_by.disabled = true;\n";
	echo "\twindow.document.frmInsert._bank[0].disabled = true;\n";
	echo "\twindow.document.frmInsert._bank[1].disabled = true;\n";
	echo "\twindow.document.frmInsert._bank[2].disabled = true;\n";
	echo "\twindow.document.frmInsert._bank[3].disabled = true;\n";
	echo "\twindow.document.frmInsert._bank[4].disabled = true;\n";
	echo "\twindow.document.frmInsert._bank[5].disabled = true;\n\n";
}
?>
	setSelect(window.document.frmInsert._payment_sj_inv_fp_tender, "<?php echo $column['turn_payment_sj_inv_fp_tender'] ?>");
	setSelect(window.document.frmInsert._type, "<?php echo $column['turn_type_return'] ?>");
	updateAmount();
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE RETURN<br />
</strong>
<small class="comment">* Issue invoice return &amp; receive item | Unconfirmed incoming item</small>
<hr><br />
<form name="frmInsert" method="post">
<input type="hidden" name="p_mode">
<input type='hidden' name="_code" value="<?php echo rtrim($column['turn_code'])?>">
<input type='hidden' name="_bill_code" value="<?php echo $column['turn_bill_code']?>">
<input type='hidden' name="_paper" value="<?php echo $column['turn_paper']?>">
<input type='hidden' name="_std_idx" value="<?php echo $column['std_idx']?>">
<input type='hidden' name="_inc_idx" value="<?php echo $column['inc_idx']?>">
<input type='hidden' name="_return_condition" value="<?php echo $column['turn_return_condition']?>">
<input type='hidden' name="_dept" value="<?php echo $column['turn_dept']?>">
<input type='hidden' name="_revision_time" value="<?php echo $column['turn_revesion_time']?>">
<input type='hidden' name="_old_total_amount" value="<?php echo $column['turn_total_return']?>">
<input type='hidden' name="_cus_to" value="<?php echo $column['turn_cus_to']?>">
<input type='hidden' name="_ship_to" value="<?php echo $column['turn_ship_to']?>">
<input type='hidden' name="_bill_date" value="<?php echo $column['turn_bill_inv_date']?>">
<input type='hidden' name="_bill_vat_inv_no" value="<?php echo $column['turn_bill_vat_inv_no']?>">
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="4"><strong class="bar_bl">RETURN INFORMATION</strong></td>
		<td colspan="2" align="right">
			<I>Last updated by : <?php echo ucfirst($column['turn_lastupdated_by_account']).date(', j-M-Y g:i:s', strtotime($column['turn_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th>RETURN CODE</th>
		<td colspan="2"><b><?php echo $column["turn_code"] ?></b></td>
		<th>RETURN DATE</th>
		<td><input type="text" name="_return_date" class="reqd" value="<?php echo date('d-M-Y',strtotime($column['turn_return_date'])) ?>"></td>
	</tr>
	<tr>
		<th>TYPE</th>
		<td colspan="2">
			<select name="_type" class="req" disabled>
				<option value="RO">Return</option>
				<option value="RR">Return Replace</option>
			</select> <b>[ <?php echo $column['turn_return_condition'] ?> ]</b>
		</td>
		<th>RECEIVED BY</th>
		<td><input type="text" name="_received_by" class="req" value="<?php echo $column['turn_received_by']?>"></td>
	</tr>
	<tr>
		<th rowspan="3" width="12%">CUSTOMER</th>
		<th width="12%"><font color="#696969">CODE</font></th>
		<td width="25%"><b><?php echo $column['turn_cus_to'] ?></b></td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_cus_name" class="req" style="width:100%" value="<?php echo $column['turn_cus_to_name'] ?>"></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_cus_attn" class="fmt" style="width:100%" value="<?php echo $column['turn_cus_to_attn'] ?>"></td>
		<th>NPWP</th>
		<td><input type="text" name="_cus_npwp" class="fmt" style="width:100%" value="<?php echo $column['turn_npwp'] ?>"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt" style="width:100%" value="<?php echo $column['turn_cus_to_address'] ?>"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><font color="#696969">CODE</font></th>
		<td><b><?php echo $column['turn_ship_to'] ?></b></td>
		<th>NAME</th>
		<td><input type="text" name="_ship_name" class="fmt" size="50" value="<?php echo $column['turn_ship_to_name'] ?>"></td>
	</tr>
</table><br />
<strong>REFERENCE INFORMATION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="7" width="12%" valign="top">INVOICE REF.</th>
		<th width="12%"><font color="#696969">CODE</font></th>
		<td width="25%"><a href="detail_billing.php?_code=<?php echo $column['turn_bill_code'] ?>" target="_blank"><b><?php echo $column['turn_bill_code'] ?></b></a></td>
		<th width="15%">DATE</th>
		<td><?php echo $column['bill_date'] ?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td>
			<input type="radio" name="_btnVat" value="1"<?php echo ($column['turn_vat']>0)? ' checked':'' ?> disabled><input name="_vat_value" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['turn_vat'] ?>" readonly>%
			<input type="radio" name="_btnVat" value="0"<?php echo ($column['turn_vat']<=0)? ' checked':'' ?> disabled>NON VAT
		</td>
		<th>VAT INV NO</th>
		<td><?php echo $column['turn_bill_vat_inv_no'] ?></td>
	</tr>
	<tr>
		<th>PAID BILLING</th>
		<td>
			<input type="radio" name='_is_bill_paid' value='1'<?php echo ($column['turn_is_bill_paid']=='1')? ' checked':'' ?> disabled>YES &nbsp; &nbsp;
			<input type="radio" name='_is_bill_paid' value='0'<?php echo ($column['turn_is_bill_paid']=='0')? ' checked':'' ?> disabled>NO
		</td>
		<th>MONEY BACK</th>
		<td>
			<input type="radio" name='_is_money_back' value='1'<?php echo ($column['turn_is_money_back']=='1')? ' checked':'' ?> disabled>YES &nbsp; &nbsp;
			<input type="radio" name='_is_money_back' value='0'<?php echo ($column['turn_is_money_back']=='0')? ' checked':'' ?> disabled>NO
		</td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td><input type="text" name="_do_code" class="fmt" value="<?php echo $column['bill_do_no'] ?>"></td>
		<th>DO DATE</th>
		<td><input type="text" name="_do_date" class="fmtd" value="<?php echo $column['bill_do_date'] ?>"></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td><input type="text" name="_sj_code" class="fmt" value="<?php echo $column['bill_sj_code'] ?>"></td>
		<th>SJ DATE</th>
		<td><input type="text" name="_sj_date" class="fmtd" value="<?php echo $column['bill_sj_date'] ?>"></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td><input type="text" name="_po_no" class="fmt" value="<?php echo $column['bill_po_no']?>"></td>
		<th>PO DATE</th>
		<td><input type="text" name="_po_date" class="fmtd" value="<?php echo $column['bill_po_date'] ?>"></td>
	</tr>
</table><br />
<strong class="info">[<font color="#315c87">WAREHOUSE</font>] ITEM LIST</strong>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="7%">CODE</th>
			<th width="7%">FOR</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="7%">(x)</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr id="<?php echo trim($items[0]).'-'.trim($items[1])?>">
			<td><input type="hidden" name="_wh_it_code[]" value="<?php echo trim($items[0])?>"><?php echo $items[0]?></td>
			<td><input type="hidden" name="_wh_it_code_for[]" value="<?php echo trim($items[1])?>"><?php echo $items[1]?></td>
			<td><input type="text" class="fmt" style="width:100%" name="_wh_it_model_no[]" value="<?php echo $items[2]?>" readonly></td>
			<td><input type="text" class="fmt" style="width:100%" name="_wh_it_desc[]" value="<?php echo $items[3]?>" readonly></td>
			<td><input type="text" class="reqn" style="width:100%" name="_wh_it_qty[]" value="<?php echo number_format($items[4],2)?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readonly></td>
			<td><input type="text" class="fmtn" style="width:100%" name="_wh_it_function[]" value="<?php echo number_format($items[5],2)?>" onKeyUp="formatNumber(this,'dot')"></td>
			<td><input type="text" class="fmt" style="width:100%" name="_wh_it_remark[]" value="<?php echo $items[6]?>" style="width:100%"></td>
			<td align="center"><a href="javascript:deleteWHItem('<?php echo trim($items[0]).'-'.trim($items[1])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="27%">&nbsp;</th>
	</tr>
</table><br />
<strong class="info">[<font color="#315c87">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th width="28%">DESCRIPTION</th>
			<th width="10%">UNIT PRICE<br />(Rp)</th>
			<th width="5%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="12%">REMARK</th>
			<th width="5%" colspan="2">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
			<td><input type="text" class="fmt" name="_cus_it_model_no[]" value="<?php echo $items[1]?>" style="width:100%" readonly></td>
			<td><input type="text" class="fmt" name="_cus_it_desc[]" value="<?php echo $items[2]?>" style="width:100%" readonly></td>
			<td><input type="text" class="reqn" name="_cus_it_unit_price[]" value="<?php echo number_format($items[3])?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
			<td><input type="text" class="reqn" name="_cus_it_qty[]" value="<?php echo $items[4]?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readonly></td>
			<td><input type="text" class="reqn" name="_cus_it_amount[]" value="<?php echo number_format($items[5])?>" style="width:100%" readonly></td>
			<td><input type="text" class="fmt" name="_cus_it_remark[]" value="<?php echo $items[6]?>" style="width:100%"></td>
			<td align="center">
				<a href="javascript:deleteCusItem('<?php echo trim($items[0])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
				<input type="hidden" name="_cus_it_icat_midx[]" value="<?php echo $items[8]?>">
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="left">
		<?php if($column['turn_dept']=='A') { ?>
		BASIC GROUP DISC PRICE: <input name="_basic_disc_ptc" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $disc[0]?>" readonly="readonly">%
		<?php } ?>
		</th>
		<th align="right">SUB TOTAL</th>
		<th width="7%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="13%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="18%">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">DISC %</th>
		<th><input name="_disc" type="text" class="reqn" style="width:100%" value="0" onBlur="updateAmount()"></th>
		<th><input name="totalDisc" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">Before Vat</th>
		<th><input name="total2" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">VAT</th>
		<th><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">Delivery Cost</th>
		<th><input name="totalDelivery" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">GRAND TOTAL</th>
		<th><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
</table><br>
<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2" value="<?php echo $column['turn_delivery_warehouse']?>">ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="<?php echo $column['turn_delivery_franco']?>">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" value="<?php echo $column['turn_delivery_by']?>" size="6" class="fmt"></td>
		<td><input type="checkbox" name="_delivery_chk[]" value="1" <?php echo ($column['turn_delivery_chk'] & 1)? "checked":""?> disabled>Freight charge:<input type="text" name="_delivery_freight_charge" size="8" value="<?php echo ($column['turn_delivery_freight_charge'] <= 0) ? '' : number_format($column['turn_delivery_freight_charge'])?>" class="fmtn" onKeyUp="formatNumber(this,'dot')" disabled></td>
	</tr>
	<tr>
		<th rowspan="4" width="12%">PAYMENT</th>
		<td>1.<input type="checkbox" name="_payment_chk[]" value="1" <?php echo ($column['turn_payment_chk'] & 1)? "checked":""?>>COD</td>
		<td>2.<input type="checkbox" name="_payment_chk[]" value="2" <?php echo ($column['turn_payment_chk'] & 2)? "checked":""?>>PREPAID</td>
		<td>3.<input type="checkbox" name="_payment_chk[]" value="4" <?php echo ($column['turn_payment_chk'] & 4)? "checked":""?>>Consignment</td>
		<td>4.<input type="checkbox" name="_payment_chk[]" value="8" <?php echo ($column['turn_payment_chk'] & 8)? "checked":""?>>Free/TO/LF/RP/PT</td>
	</tr>
	<tr>
		<td>5. Within 
		  <input name="_payment_widthin_days" type="text" class="fmtn" size="2" value="<?php echo $column['turn_payment_widthin_days']?>">
		days after</td>
		<td>5a.
			<select name="_payment_sj_inv_fp_tender" class="fmt">
				<option value=""></option>
				<option value="Invoice">INVOICE</option>
				<option value="Surat Jalan">SURAT JALAN</option>
				<option value="Tukar Faktur">TUKAR FAKTUR</option>
			</select>
		</td>
		<td>5b. Closing on <input name="_payment_closing_on" type="text" class="fmtd" size="10" value="<?php echo empty($column['turn_payment_closing_on']) ? "" : date("j-M-Y",strtotime($column['turn_payment_closing_on']))?>"></td>
		<td><input type="text" name="_payment_for_the_month_week" class="fmt" size="2" maxlength="2" value="<?php echo $column['turn_payment_for_the_month_week'] ?>">For the Month/Week(M/W)</td>
	</tr>
	<tr>
		<td>by 1)<input type="checkbox" name="_payment_chk[]" value="16" <?php echo ($column['turn_payment_chk'] & 16)? "checked":""?> onClick="enabledText(this, 'cash')">Cash</td>
		<td>2)<input type="checkbox" name="_payment_chk[]" value="32" <?php echo ($column['turn_payment_chk'] & 32)? "checked":""?> onClick="enabledText(this, 'check')">Check</td>
		<td>3)<input type="checkbox" name="_payment_chk[]" value="64" <?php echo ($column['turn_payment_chk'] & 64)? "checked":""?> onClick="enabledText(this, 'transfer')">Transfer</td>
		<td>4)<input type="checkbox" name="_payment_chk[]" value="128" <?php echo ($column['turn_payment_chk'] & 128)? "checked":""?>  onClick="enabledBankOption(this)">Giro</td>
	</tr>
	<tr>
		<td>by<input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" value="<?php echo $column['turn_payment_cash_by']?>"></td>
		<td>by<input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" value="<?php echo $column['turn_payment_check_by']?>"></td>
		<td>by<input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" value="<?php echo $column['turn_payment_transfer_by']?>"></td>
		<td>
			Issue : <input type="text" name="_payment_giro_issue" size="10" class="fmtd" value="<?php echo ($column['turn_payment_giro_issue'] != '') ? date("j-M-Y", strtotime($column['turn_payment_giro_issue'])) : ''?>">
			Due : <input type="text" name="_payment_giro_due" size="10" class="fmtd" value="<?php echo ($column['turn_payment_giro_due'] != '') ? date("j-M-Y", strtotime($column['turn_payment_giro_due'])) : ''?>">
		</td>
	</tr>
	<tr>
		<th>BANK</th>
		<td>
			<input type="radio" name="_bank" value="BCA1" id="bca1" <?php echo ($column['turn_payment_bank'] == 'BCA1') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bca1">BCA 1</label><br />
			<input type="radio" name="_bank" value="BCA2" id="bca2" <?php echo ($column['turn_payment_bank'] == 'BCA2') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bca2">BCA 2</label><br />
			<input type="radio" name="_bank" value="MANDIRI" id="mandiri" <?php echo ($column['turn_payment_bank'] == 'MANDIRI') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="mandiri">Mandiri</label><br />
		</td>
		<td>
			<input type="radio" name="_bank" value="BII1" id="bii1" <?php echo ($column['turn_payment_bank'] == 'BII1') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bii1">BII 1</label><br />
			<input type="radio" name="_bank" value="BII2" id="bii2" <?php echo ($column['turn_payment_bank'] == 'BII2') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bii2">BII 2</label><br />
			<input type="radio" name="_bank" value="DANAMON" id="danamon" <?php echo ($column['turn_payment_bank'] == 'DANAMON') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="danamon">Danamon</label>
		</td>
		<td colspan="2">
			<textarea name="_bank_address" rows="3" style="width:100%" readonly><?php echo $column['turn_payment_bank_address'] ?></textarea>
		</td>
	</tr>
	<tr>
		<th>DATE</th>
		<td colspan="2">Tukar Faktur : <input type="text" name="_tukar_faktur_date" class="fmt" value="<?php echo ($column['turn_tukar_faktur_date'] != '') ? date("j-M-Y", strtotime($column['turn_tukar_faktur_date'])) : ''?>" disabled></td>
	</tr>
</table><br />
<strong class="info">OTHERS</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">SIGN BY</th>
		<td><input type="text" name="_signature_by" class="req" value="<?php echo $column['turn_signature_by'] ?>"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td><textarea name="_remark" rows="4" style="width:100%"><?php echo $column['turn_remark'] ?></textarea></td>
	</tr>
</table>
</form>
<div align="right">
	<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
</div><br />
<script language="javascript" type="text/javascript">
	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_billing_by_customer.php?_cus_code=". $column['turn_ship_to'] ?>';
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