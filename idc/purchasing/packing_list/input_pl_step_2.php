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
$left_loc = "input_pl_step_1.php";

//PROCESS FORM
require_once APP_DIR . "_include/purchasing/tpl_process_form_pl.php";

//-------------------------------------------------------------------------------------------- DEFAULT PROCESS
//default max qty each it_code
$sql_qty = "
  SELECT
	po.it_code AS it_code,
	po.poit_qty AS it_qty,
	po.poit_unit_price AS it_price
  FROM ".ZKP_SQL."_tb_po AS a JOIN ".ZKP_SQL."_tb_po_item AS po USING(po_code)
  WHERE a.po_code = '$_po_code'
UNION
  SELECT 
	pl.it_code AS it_code,
	SUM(-pl.plit_qty) AS it_qty,
	pl.plit_unit_price AS it_price
  FROM ".ZKP_SQL."_tb_pl AS b JOIN ".ZKP_SQL."_tb_pl_item AS pl USING(pl_idx)
  WHERE b.po_code = '$_po_code'
  GROUP BY it_code, it_price
ORDER BY it_code";
$total  = 0;
$qty	= array();
$res	= query($sql_qty);
while($col =& fetchRowAssoc($res)) {
	//calculate remain qty in each it_code 
	if(!isset($qty[$col['it_code']])) {
		$qty[$col['it_code']] = $col['it_qty'];
	} else {
		$qty[$col['it_code']] += $col['it_qty'];
	}
	$total += $col['it_qty'];
}

if ($total == 0) {
	$o = new ZKError ("COMPLETED_PO", "COMPLETED_PO", "All items in <b>$_po_code</b> have been issued Packing List");
	$M->goErrorPage($o, HTTP_DIR . "$currentDept/$moduleDept/input_pl_step_1.php");
}

$sql_item = "
SELECT 
 pi.icat_midx, 			--0
 pi.it_code,			--1
 pi.poit_item,			--2
 pi.poit_desc,			--3
 pi.poit_qty,			--4
 pi.poit_unit_price,	--5
 pi.poit_unit_price*pi.poit_qty AS amount,	--6	
 pi.poit_attribute,		--7
 pi.poit_remark			--8
FROM ".ZKP_SQL."_tb_po AS po JOIN ".ZKP_SQL."_tb_po_item AS pi USING(po_code)
WHERE po.po_code = '$_po_code'
ORDER BY pi.it_code";
$res_item	=& query($sql_item);
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
function seeDetailPO() {

	var x = (screen.availWidth - 470) / 2;
	var y = (screen.availHeight - 600) / 2;
	var po_code = '<?php echo $_po_code?>';

	var win = window.open(
		'./p_detail_po.php?_code='+ po_code,
		'',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function checkform(o) {

	if (window.rowPosition.rows.length == 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;
	var idx_qty		= 26;
	var e			= window.document.frmInsert.elements;

	for (var i=0; i<numItem; i++) {
		var value	= parseFloat(removecomma(e(idx_qty+i*numInput).value));
		if(value == 0) {
			alert("Please delete item with 0 (Null) value qty");
			return;
		}
	}

	if (verify(o)) {
		if(confirm("Are you sure to save?")) {
			o.submit();
		}
	}
}

//Delete Item wtd rows collection
function deleteItem(idx) {
	var count = window.rowPosition.rows.length;

	if (window.rowPosition.rows.length == 1) {
		alert("You need to leave at least 1 item");
		return;
	}

	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
		}
	}
	updateAmount();
}

function checkQty(max_value, item){
	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var count		= window.rowPosition.rows.length;
	var numInput	= 9;
	var idx_qty		= 26;
	var e			= window.document.frmInsert.elements;
	var it_code		= item;

	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == item) {
			var idx = i;
		}
	}

	var value		= parseFloat(removecomma(e(idx_qty+idx*numInput).value));

	if(value > max_value) {
		alert("Maximum qty for this item is " + addcomma(max_value) +" pcs.\n Please check the amount again");
		e(idx_qty+idx*numInput).value = addcomma(max_value);
	}

	updateAmount();
}

function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;

	var idx_price	= 25;			/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	var sumOfQty	= 0;
	var sumOfTotal	= 0;
	var e = window.document.frmInsert.elements;

	for (var i = 0; i< numItem; i++) {
		var price	= parseFloat(removecomma(e(idx_price+i*numInput).value));
		var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

		if(f._layout_type.value == '3') {
			e(idx_amount+i*numInput).value = numFormatval((price*qty/100)+'',2);
		} else {
			e(idx_amount+i*numInput).value = numFormatval((price*qty)+'',2);
		}

		sumOfQty	+= qty;
		if(f._layout_type.value == '3') {
			sumOfTotal	+= price*qty/100;
		} else {
			sumOfTotal	+= price*qty;
		}
	}

	f.totalQty.value	  = addcomma(sumOfQty);
	f.totalAmount.value   = numFormatval(sumOfTotal + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="updateAmount()">
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
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW PL (STEP 2 / 2)<br />
</strong>
<small class="comment">* Issue PL source by PO</small>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert_pl'>
<input type="hidden" name="_inv_no" value="<?php echo $_inv_no?>">
<input type="hidden" name="_inv_date" value="<?php echo $_inv_date?>">
<input type="hidden" name="_po_code" value="<?php echo $_po_code?>">
<input type="hidden" name="_po_date" value="<?php echo $_po_date?>">
<input type="hidden" name="_pl_type" value="<?php echo $_pl_type?>">
<input type="hidden" name="_shipment_mode" value="<?php echo $_shipment_mode?>">
<input type="hidden" name="_mode_desc" value="<?php echo $_mode_desc?>">
<input type="hidden" name="_layout_type" value="<?php echo $_layout_type?>">
<input type="hidden" name="_received_by" value="<?php echo addslashes($_received_by)?>">
<input type="hidden" name="_sp_code" value="<?php echo addslashes($_supplier_code)?>">
<input type="hidden" name="_sp_name" value="<?php echo $_supplier_name?>">
<input type="hidden" name="_etd_date" value="<?php echo $_etd_date?>">
<input type="hidden" name="_eta_date" value="<?php echo $_eta_date?>">
	<span class="bar_bl">INVOICE INFORMATION</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="12%">INVOICE NO</th>
			<td width="38%"><?php echo $_inv_no ?></td>
			<th width="15%">INVOICE DATE</th>
			<td><?php echo $_inv_date ?></td>
		</tr>
		<tr>
			<th>ETD DATE</th>
			<td><?php echo $_etd_date ?></td>
			<th>ETA DATE</th>
			<td><?php echo $_eta_date ?></td>
		</tr>
		<tr>
			<th width="15%">RECEIVED BY</th>
			<td width="34%"><?php echo $_received_by ?></td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th rowspan="3" width="12%">SUPPLIER</th>
			<th width="12%">CODE</th>
			<td><?php echo $_supplier_code ?></td>
			<th width="15%">NAME</th>
			<td><?php echo $_supplier_name ?></td>
		</tr>
		<tr>
			<th width="12%">ATTN</th>
			<td><?php echo $_supplier_attn ?></td>
			<th width="12%">CC</th>
			<td><?php echo $_supplier_cc ?></td>
		</tr>
		<tr>
			<th>TELP</th>
			<td><?php echo $_supplier_phone ?></td>
			<th>FAX</th>
			<td><?php echo $_supplier_fax ?></td>
		</tr>
		<tr>
			<th rowspan="3">PO<br />REFERENCE</th>
			<th>PO NO</th>
			<td>
				<?php echo $_po_code ?> &nbsp; &nbsp;
				<button name="btnDetail" class="fmt" onclick="seeDetailPO()">DETAIL</button>
			</td>
			<th>PO DATE</th>
			<td><?php echo $_po_date ?></td>
		</tr>
		<tr>
			<th width="15%">PO TYPE</th>
			<td>
				<input type="radio" name="_type" value="1" <?php echo ($_pl_type == 1) ? 'checked' : '' ?> disabled>NORMAL &nbsp;
				<input type="radio" name="_type" value="2" <?php echo ($_pl_type == 2) ? 'checked' : '' ?> disabled>DOOR TO DOOR
			</td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_mode" value="sea" <?php echo ($_shipment_mode == 'sea') ? 'checked' : '' ?> disabled>SEA &nbsp;
				<input type="radio" name="_mode" value="air" <?php echo ($_shipment_mode == 'air') ? 'checked' : '' ?> disabled>AIR &nbsp;
				<input type="radio" name="_mode" value="other" <?php echo ($_shipment_mode == 'other') ? 'checked' : '' ?> disabled>OTHER
				<input type="text" name="_desc" class="fmt" size="10" maxlength="15" value="<?php echo $_mode_desc ?>" disabled>
			</td>
		</tr>
	</table><br />
	<span class="bar_bl">ITEM LIST</span>
	<table width="100%" class="table_box">
		<thead>
			<tr>
				<th width="5%">CODE</th>
				<th width="17%">ITEM</th>
				<th>DESC</th>
				<th width="5%">ATT</th>
				<th width="10%">UNIT PRICE<br />US$</th>
				<th width="8%">QTY</th>
				<th width="10%">AMOUNT<br />US$</th>
				<th width="11%">REMARK</th>
				<th width="5%">DEL</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
while($items =& fetchRow($res_item)) {
	if($qty[$items[1]] > 0) {
?>
	
			<tr id="<?php echo trim($items[1])?>">
				<td><?php echo $items[1]?><input type="hidden" name="_it_code[]" value="<?php echo $items[1]?>"></td>
				<td><input type="text" name="_plit_item[]" value="<?php echo $items[2]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_plit_desc[]" value="<?php echo $items[3]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_plit_att[]" value="<?php echo $items[7]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_plit_unit_price[]" value="<?php echo number_format($items[5],2)?>" style="width:100%" class="reqn" readonly></td>
				<td><input type="text" name="_plit_qty[]" value="<?php echo  number_format($qty[$items[1]])?>" style="width:100%" class="reqn" onBlur="checkQty(<?php echo $qty[$items[1]].",'".trim($items[1])."'"?>)" onKeyUp="formatNumber(this,'dot')"></td>
				<td><input type="text" name="_plit_amount[]" value="<?php echo number_format($items[6],2)?>" style="width:100%" class="reqn" readonly></td>
				<td><input type="text" name="_plit_remark[]" value="<?php echo $items[8]?>" style="width:100%" class="fmt"></td>
				<td align="center">
					<input type="hidden" name="_icat_midx[]" value="<?php echo $items[0]?>">
					<a href="javascript:deleteItem('<?php echo trim($items[1])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
				</td>
			</tr>
<?php }} ?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">GRAND TOTAL</th>
			<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="10%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="16%">&nbsp;</th>
		</tr>
	</table><br>
	<span class="bar_bl">OTHERS</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">REMARK</th>
			<td><textarea name="_remark" style="width:100%" rows="3"></textarea></td>
		</tr>
	</table>
<input type="hidden" name="_ordered_by" value="<?php echo $_ordered_by ?>">
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save PL"> &nbsp; Save PL</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_pl_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel PL"> &nbsp; Cancel PL</button>
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