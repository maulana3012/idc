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
ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . "/packing_list/index.php");

//GLOBAL
$left_loc = "summary_pl_by_supplier.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . $currentDept . '/packing_list/summary_pl_by_supplier.php');
} else {
	$_code = urldecode($_GET['_code']);
}

//------------------------------------------------------------------------------------------------- delete PL
if (ckperm(ZKP_DELETE, HTTP_DIR . $currentDept . "/packing_list/index.php", 'delete')) {

	$result =& query("
		DELETE FROM ".ZKP_SQL."_tb_po_recap WHERE rcp_pl_code = $_code;	
		DELETE FROM ".ZKP_SQL."_tb_pl_item WHERE pl_idx = $_code;
		DELETE FROM ".ZKP_SQL."_tb_pl WHERE pl_idx = $_code;
		DELETE FROM ".ZKP_SQL."_tb_pending_pl WHERE pl_idx = $_code;
		
	");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . $currentDept . "/packing_list/revise_pl.php?_code=".urlencode($_code));
	}
	goPage(HTTP_DIR . $currentDept . '/packing_list/summary_pl_by_supplier.php');
}

//------------------------------------------------------------------------------------------------- update PL
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . '/packing_list/index.php', 'update')) {

	$_code			= $_POST['_code'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name	 	= $_POST['_sp_name'];
	$_inv_no	 	= $_POST['_inv_no'];
	$_inv_date	 	= $_POST['_inv_date'];
	$_po_code	 	= $_POST['_po_code'];
	$_etd_date	 	= $_POST['_etd_date'];
	$_eta_date	 	= $_POST['_eta_date'];
	$_layout_type	= $_POST['_layout_type'];
	$_shipment_mode	= $_POST['_mode'];
	$_pl_type	= $_POST['_pl_type'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_received_by	= $_POST['_received_by'];
	$_total_qty		= $_POST['totalQty'];
	$_remark		= $_POST['_remark'];
	$_lastupdated_by_account = $S->getValue("ma_account");

	//Item Value
	foreach($_POST['_icat_midx'] as $val)		$_icat_midx[]	= $val;
	foreach($_POST['_it_code'] as $val)			$_it_code[]		= $val;
	foreach($_POST['_plit_item'] as $val)		$_plit_item[]	= $val;
	foreach($_POST['_plit_desc'] as $val)		$_plit_desc[]		= $val;
	foreach($_POST['_plit_qty'] as $val)		$_plit_qty[] 		= $val;
	foreach($_POST['_plit_unit_price'] as $val)	$_plit_unit_price[] = $val;
	foreach($_POST['_plit_remark'] as $val)		$_plit_remark[]		= $val;
	foreach($_POST['_plit_att'] as $val)		$_plit_att[] 		= $val;

	//make pgsql ARRAY String for many item
	$_icat_midx		= implode(',', $_icat_midx);
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_plit_item		= '$$' . implode('$$,$$', $_plit_item) . '$$';
	$_plit_desc		= '$$' . implode('$$,$$', $_plit_desc) . '$$';
	$_plit_qty		= implode(',', $_plit_qty);
	$_plit_unit_price = implode(',', $_plit_unit_price);
	$_plit_remark	= '$$' . implode('$$,$$', $_plit_remark) . '$$';
	$_plit_att		= '$$' . implode('$$,$$', $_plit_att) . '$$';

	//update PL
	$result = executeSP(
		ZKP_SQL."_updatePL",
		$_code,
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		"$\${$_po_code}$\$",
		"$\${$_sp_code}$\$",
		"$\${$_inv_no}$\$",
		"$\${$_inv_date}$\$",
		"$\${$_etd_date}$\$",
		"$\${$_eta_date}$\$",
		$_layout_type,
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_shipment_mode}$\$",
		"$\${$_mode_desc}$\$",
		$_pl_type,
		$_total_qty,
		"$\${$_remark}$\$",
		"ARRAY[$_icat_midx]",
		"ARRAY[$_it_code]",
		"ARRAY[$_plit_item]",
		"ARRAY[$_plit_desc]",
		"ARRAY[$_plit_unit_price]",
		"ARRAY[$_plit_qty]",
		"ARRAY[$_plit_remark]",
		"ARRAY[$_plit_att]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . $currentDept . "/packing_list/revise_pl.php?_code=$_code");
	} $M->goPage(HTTP_DIR . $currentDept . "/packing_list/revise_pl.php?_code=".$_code);
}

//-------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT *, ".ZKP_SQL."_isArrivedPL(1,pl_idx) AS is_arrived, po.po_date FROM ".ZKP_SQL."_tb_pl AS pl JOIN ".ZKP_SQL."_tb_po AS po USING(po_code) WHERE pl.pl_idx = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . $currentDept . '/packing_list/index.php');
}

$sql_item = "
SELECT 
 plit.icat_midx, 		--0
 plit.it_code,			--1
 plit.plit_item,		--2
 plit.plit_desc,		--3
 plit.plit_unit_price,	--4
 plit.plit_qty,			--5
 CASE
 	WHEN pl.pl_layout_type = 4 THEN plit.plit_unit_price*plit.plit_qty/100
 	ELSE plit.plit_unit_price*plit.plit_qty/100
 END AS amount,			--6
 plit.plit_remark,		--7
 plit.plit_attribute	--8
FROM ".ZKP_SQL."_tb_pl AS pl JOIN ".ZKP_SQL."_tb_pl_item AS plit USING(pl_idx)
WHERE pl.pl_idx = $_code
ORDER BY plit.it_code";
$res_item	=& query($sql_item);

//default max qty each it_code
$sql_qty = "
  SELECT
	po.it_code AS it_code,
	po.poit_qty AS it_qty
  FROM ".ZKP_SQL."_tb_po AS a JOIN ".ZKP_SQL."_tb_po_item AS po USING(po_code)
  WHERE a.po_code = '".$column["po_code"]."'
UNION
  SELECT 
	pl.it_code AS it_code,
	SUM(-pl.plit_qty) AS it_qty
  FROM ".ZKP_SQL."_tb_pl AS b JOIN ".ZKP_SQL."_tb_pl_item AS pl USING(pl_idx)
  WHERE b.po_code = '".$column["po_code"]."'
  GROUP BY it_code
ORDER BY it_code";
$qty	= array();
$res	= query($sql_qty);
while($col =& fetchRowAssoc($res)) {
	//calculate remain qty in each it_code 
	if(!isset($qty[$col['it_code']])) {
		$qty[$col['it_code']] = $col['it_qty'];
	} else {
		$qty[$col['it_code']] += $col['it_qty'];
	}
}

//Incoming PL
$sql_pl =
"SELECT
  a.inpl_idx,
  to_char(a.inpl_checked_date,'dd-Mon-YY') AS checked_date,
  a.inpl_inv_no,
  c.it_code,
  c.it_model_no,
  c.it_desc,
  b.init_qty,
  CASE
	WHEN (select DISTINCT(inpl_idx) FROM ".ZKP_SQL."_tb_expired_pl WHERE inpl_idx = a.inpl_idx) is not null THEN true
	else false
  END AS inpl_has_ed
FROM
  ".ZKP_SQL."_tb_in_pl AS a
  JOIN ".ZKP_SQL."_tb_in_pl_item AS b USING(inpl_idx)
  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE a.pl_idx = $_code
ORDER BY a.inpl_idx, a.inpl_checked_date, c.it_code";
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res_pl = query($sql_pl);
$numRow = numQueryRows($res_pl);

while($col =& fetchRowAssoc($res_pl)) {

	$rd[] = array(
		$col['inpl_idx'],		//0
		$col['checked_date'],	//1
		$col['inpl_inv_no'],	//2
		$col['it_code'], 		//3
		$col['it_model_no'],	//4
		$col['it_desc'],		//5
		$col['init_qty'], 		//6
		$col['inpl_has_ed'] 	//7
	);

	//1st grouping
	if($cache[0] != $col['inpl_idx']) {
		$cache[0] = $col['inpl_idx'];
		$group0[$col['inpl_idx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['inpl_idx']][$col['it_code']] = 1;
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
function checkform(o) {

	if (window.rowPosition.rows.length == 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		o.submit();
	}
}

//Delete Item wtd rows collection
function deleteItem(idx) {
	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
		}
	}
	updateAmount();
}

function checkQty(max_value, i){
	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;
	var idx_qty		= 24;			/////
	var e			= window.document.frmInsert.elements;
	var value		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

	if(value > max_value) {
		alert("Maximum qty for this item is " + addcomma(max_value) +" pcs.\n Please check the amount again");
		e(idx_qty+i*numInput).value = addcomma(max_value);
	}
	updateAmount();
}

//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;
	var idx_price	= 23;			/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	var sumOfQty	= 0;
	var sumOfTotal	= 0;
	var e = window.document.frmInsert.elements;

	for (var i = 0; i< numItem; i++) {
		var price	= parseFloat(removecomma(e(idx_price+i*numInput).value));	
		var qty 	= parseFloat(removecomma(e(idx_qty+i*numInput).value));

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

function fillCode(target) {

	if(target == 'supplier') {
		var file	= './p_list_supplier.php?_name=';
		var keyword = window.document.frmInsert._sp_code.value;
	} else if(target == 'forwarder') {
		var file	= './p_list_forwarder.php?_name=';
		var keyword = window.document.frmInsert._fw_code.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		file + keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function initPage() {
	updateAmount();

	var f = window.document.frmInsert;
	if(f._mode[2].checked == true) {
		f._mode_desc.readOnly = false;
	}

	<?php if($column['is_arrived']=='t') { ?>
	window.document.all.btnDelete.disabled = true;
	window.document.all.btnUpdate.disabled = true;
	<?php } ?>
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="initPage()">
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
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL PL<br />
</strong>
<small class="comment">* Source by PO</small>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type="hidden" name="_code" value="<?php echo $column['pl_idx']?>">
<input type="hidden" name="_po_code" value="<?php echo $column['po_code']?>">
<input type="hidden" name="_pl_type" value="<?php echo $column['pl_type']?>">
<input type="hidden" name="_shipment_mode" value="<?php echo $column['pl_shipment_mode']?>">
<input type="hidden" name="_layout_type" value="<?php echo $column['pl_layout_type']?>">
	<table width="100%" class="table_box">
		<tr>
			<td colspan="2"><strong class="info">PL INFORMATION</strong></td>
			<td colspan="2" align="right">
				<I>Last updated by : <?php echo $column['pl_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['pl_lastupdated_timestamp']))?></I>
			</td>
		</tr>
		<tr>
			<th width="12%">INVOICE NO</th>
			<td width="40%"><input name="_inv_no" type="text" class="req" size="15" maxlength="64" value="<?php echo $column["pl_inv_no"] ?>"></td>
			<th width="15%">INVOICE DATE</th>
			<td><input name="_inv_date" type="text" class="reqd" size="15" maxlength="64" value="<?php echo date('j-M-Y', strtotime($column['pl_inv_date']))?>"></td>
		</tr>
		<tr>
			<th>ETD DATE</th>
			<td><input type="text" name="_etd_date" class="reqd" size="15" value="<?php echo date('j-M-Y', strtotime($column['pl_etd_date'])) ?>"></td>
			<th>ETA DATE</th>
			<td><input type="text" name="_eta_date" class="reqd" size="15" value="<?php echo date('j-M-Y', strtotime($column['pl_eta_date'])) ?>"></td>
		</tr>
		<tr>
			<th width="15%">RECEIVED BY</th>
			<td><input name="_received_by" type="text" class="req" size="20" maxlength="32" value="<?php echo $column["pl_received_by"]?>"></td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th width="12%">SUPPLIER</th>
			<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
			<td width="28%"><input type="text" name="_sp_code" class="req" size="6" maxlength="4" value="<?php echo $column['pl_sp_code']?>"></td>
			<th width="15%">NAME</th>
			<td><input type="text" name="_sp_name" class="req" style="width:100%" value="<?php echo $column['pl_sp_name']?>"></td>
		</tr>
		<tr>
			<th rowspan="3">PO<br />REFERENCE</th>
			<th>PO NO</th>
			<td>
				<a href="../purchasing/detail_po.php?_code=<?php echo trim($column["po_code"]) ?>" target="_blank"><b><b><?php echo $column["po_code"] ?></b></b></a>
			</td>
			<th>PO DATE</th>
			<td><?php echo date('j-M-Y', strtotime($column['po_date'])) ?></td>
		</tr>
		<tr>
			<th width="15%">PO TYPE</th>
			<td>
				<input type="radio" name="_type" value="1" <?php echo ($column["pl_type"] == 1) ? 'checked' : '' ?> disabled>NORMAL &nbsp;
				<input type="radio" name="_type" value="2" <?php echo ($column["pl_type"] == 2) ? 'checked' : '' ?> disabled>DOOR TO DOOR
			</td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_mode" value="sea" <?php echo (trim($column["pl_shipment_mode"]) == 'sea') ? 'checked' : '' ?>>SEA &nbsp;
				<input type="radio" name="_mode" value="air" <?php echo (trim($column["pl_shipment_mode"]) == 'air') ? 'checked' : '' ?>>AIR &nbsp;
				<input type="radio" name="_mode" value="other" <?php echo (trim($column["pl_shipment_mode"]) == 'other') ? 'checked' : '' ?>>OTHER
				<input type="text" name="_mode_desc" class="fmt" size="10" maxlength="15" value="<?php echo $column["pl_shipment_desc"] ?>">
			</td>
		</tr>
	</table><br />
	<strong class="info">ITEM LIST</strong>
	<table width="100%" class="table_box">
		<thead>
			<tr>
				<th width="5%">CODE</th>
				<th width="17%">ITEM</th>
				<th>DESC</th>
				<th width="8%">ATT</th>
				<th width="10%">UNIT PRICE<br />US$</th>
				<th width="8%">QTY</th>
				<th width="10%">AMOUNT<br />US$</th>
				<th width="11%">REMARK</th>
				<th width="5%">DEL</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
$i = 0;
while($items =& fetchRow($res_item)) {
	$max = $qty[$items[1]]+$items[5];
?>
			<tr id="<?php echo trim($items[1])?>">
				<td><?php echo $items[1]?><input type="hidden" name="_it_code[]" value="<?php echo $items[1]?>"></td>
				<td><input type="text" name="_plit_item[]" value="<?php echo $items[2]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_plit_desc[]" value="<?php echo $items[3]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_plit_att[]" value="<?php echo trim($items[8])?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_plit_unit_price[]" value="<?php echo number_format($items[4],2)?>" style="width:100%" class="reqn" readonly></td>
				<td><input type="text" name="_plit_qty[]" value="<?php echo number_format($items[5])?>" style="width:100%" class="reqn" onBlur="checkQty(<?php echo $max.",".$i++?>)" onKeyUp="formatNumber(this,'dot')"></td>
				<td><input type="text" name="_plit_amount[]" value="<?php echo number_format($items[6],2)?>" style="width:100%" class="reqn" readonly></td>
				<td><input type="text" name="_plit_remark[]" value="<?php echo $items[7]?>" style="width:100%" class="fmt"></td>
				<td align="center">
					<input type="hidden" name="_icat_midx[]" value="<?php echo $items[0]?>">
					<a href="javascript:deleteItem('<?php echo trim($items[1])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
				</td>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">GRAND TOTAL</th>
			<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="10%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="16%">&nbsp;</th>
		</tr>
	</table><br />
	<strong class="info">OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">REMARK</th>
			<td><textarea name="_remark" style="width:100%" rows="3"><?php echo $column["pl_remark"] ?></textarea></td>
		</tr>
	</table><br /><br />
</form>
<!------------------------------------------ START PRINT INCOMING PL ------------------------------------------>
<?php if($column['pl_idx'] != '') { 
	$part = "detail_pl_pl";
	include "../../_include/purchasing/tpl_detail_po.php";  
}
?>
<!------------------------------------------ END PRINT INCOMING PL ------------------------------------------>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_btn' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete PL"> &nbsp; Delete PL</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update PL"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . $currentDept . '/packing_list/summary_pl_by_supplier.php' ?>';
	}

</script>
<!--END Button-->
<!---------------------------------------- start print lock document ---------------------------------------->
<?php if($column['is_arrived']=='t') { ?>
<table width="100%" class="table_box">
	<tr>
		<td width="10%" valign="top"><img src="../../_images/icon/warning.gif"></td>
		<td>
			<span>
			This is a locked document. To modify document, see the hierarchy process.<br /><br />
			Here are the possibility(es) :
			</span>
			<ul>
				<li> This PL already come and confirmed by warehouse</li>
			</ul>
		</td>
	</tr>
</table><br /><br />
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