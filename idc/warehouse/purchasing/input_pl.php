<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc	= "input_pl.php";
$_code		= isset($_GET["_code"]) ? $_GET["_code"] : "";

//------------------------------------------------------------------------------------------------------------------------------------------------------- INSERT PROCESS
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_po_code	 	= $_POST['_po_code'];
	$_pl_no			= $_POST['_pl_no'];
	$_pl_date		= $_POST['_pl_date'];
	$_issued_by		= $_POST['_issued_by'];
	$_delivery_date	= $_POST['_deli_date'];
	$_remark		= $_POST['_remark'];
	$_lastupdated_by_account = $S->getValue("ma_account");

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_plit_qty'] as $val)		$_plit_qty[] 		= $val;

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_plit_qty		= implode(',', $_plit_qty);

	$result = executeSP(
		ZKP_SQL."_insertPLLocal",
		"$\${$_po_code}$\$",
		"$\${$_pl_no}$\$",
		"$\${$_pl_date}$\$",
		"$\${$_issued_by}$\$",
		"$\${$_delivery_date}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_plit_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_pl.php");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_pl.php?_code=$_code&_pl_no=$_pl_no");
}

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql = "SELECT *, ".ZKP_SQL."_getPLLocalNo(po_code) as pl_no FROM ".ZKP_SQL."_tb_po_local WHERE po_code='$_code'";
$result	= query($sql);
$column = fetchRowAssoc($result);
$numRow = numQueryRows($result);

$sql_item	= "
SELECT 
  it_code,
  it_model_no, 
  it_desc, 
  poit_unit, 
  ".ZKP_SQL."_availablePLLocalQty('$_code', it_code, null) AS plit_qty
FROM
  ".ZKP_SQL."_tb_po_local_item
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
WHERE po_code = '$_code' AND ".ZKP_SQL."_availablePLLocalQty('$_code', it_code, null) > 0
GROUP BY it_code, it_model_no, it_desc, poit_unit
ORDER BY it_code";
$res_item	= query($sql_item);
$numRowItem = numQueryRows($res_item);
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
<?php
// Print Javascript Code
echo "var avail_qty = new Array();\n";
while($rows =& fetchRow($res_item)) {
	printf("avail_qty['%s']= %s;\n",
		addslashes($rows[0]), //code from query
		$rows[4]
	);
}
?>

function checkform(o) {

	if (window.rowPosition.rows.length == 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save?")) {
			o.submit();
		}
	}
}

function fillPO() {
	var x = (screen.availWidth - 470) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'p_list_po.php', '',
		'scrollbars,width=470,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Delete Item wtd rows collection
function deleteItem(idx) {

	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1; //decrease loop - 1
		}
	}
	updateAmount();
}

function checkAmount(idx, qty) {

	if(parseInt(removecomma(qty)) > avail_qty[idx]) {
		alert("Max qty for this item is "+ addcomma(avail_qty[idx]));

		var f			= window.document.frmInsert;
		var numItem		= window.rowPosition.rows.length;
		var numInput	= 5;	/////
		var idx_qty		= 15;	/////
		var e = window.document.frmInsert.elements;
		for (var i=0; i<numItem; i++) {
			var oRow = window.rowPosition.rows(i);
			if (oRow.id == idx) {
				e(idx_qty+i*numInput).value = addcomma(avail_qty[idx]);
			}
		}
	}

	updateAmount();
}

//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 5;	/////
	var idx_qty		= 15;	/////

	var e = window.document.frmInsert.elements;
	var sumOfQty	= 0;

	for (var i = 0; i< numItem; i++) {
		var qty = parseFloat(removecomma(e(idx_qty+i*numInput).value));
		sumOfQty	+= qty;
	}

	f.totalQty.value	  = addcomma(sumOfQty);
}

function initPage() {
	updateAmount();
<?php if($numRow > 0 && $numRowItem <= 0) { ?>
	window.document.all.btnSave.disabled = true;
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW PL</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<span class="bar_bl">PL INFORMATION</span>
<table width="100%" class="table_box">
	<?php if($numRow > 0 && $numRowItem <= 0) { ?>
	<tr>
		<th><img src="../../_images/icon/hint.gif"> &nbsp; <span style="font-family:Courier;color:blue;font-weight:bold">HINT</span></th>
		<th colspan="5" align="left">
			<span style="font-family:Courier;font-size:12px">All item in this PO already has PL.</span>
		</th>
	</tr>
	<?php } ?>
	<tr>
		<th width="15%">PL NO</th>
		<td width="22%">
			<input name="_po_code" type="text" class="req" size="20" onclick="fillPO()" value="<?php echo $column["po_code"] ?>" readonly> &nbsp;- &nbsp;<input name="_pl_no" type="text" class="reqn" style="width:15%" onclick="fillPO()" value="<?php echo $column["pl_no"] ?>" readonly>
		</td>
		<th width="7%">
			<a href="javascript:fillPO()"><img src="../../_images/icon/search_mini.gif" alt="Search PO"></a>&nbsp;
			<a href="revise_po.php?_code=<?php echo $_code ?>" target="_blank"><img src="../../_images/icon/list_mini.gif" alt="View detail PO"></a>
		</th>
		<td width="5%"></td>
		<th width="15%">PL DATE</th>
		<td><input name="_pl_date" type="text" class="reqd" size="15" maxlength="64" value="<?php echo date("j-M-Y")?>"></td>
	</tr>
	<tr>
		<th width="15%">ISSUED BY</th>
		<td colspan="3"><input name="_issued_by" type="text" class="req" size="15" maxlength="32" value="<?php echo ucfirst($S->getValue("ma_account"))?>"></td>
		<th>DELIVERY DATE</th>
		<td><input name="_deli_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>"></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="3" width="12%">SUPPLIER</th>
		<th width="12%">CODE</th>
		<td width="25%"><input name="_sp_code" type="text" class="req" size="6" value="<?php echo $column["sp_code"] ?>" readOnly></td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_sp_name" class="req" style="width:100%" maxlength="125" value="<?php echo $column["po_sp_name"] ?>" readOnly></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_sp_attn" class="fmt" size="25" maxlength="32" value="<?php echo $column["po_sp_attn"] ?>" readOnly></td>
		<th>CONTACT</th>
		<td>
		Telp : <input type="text" name="_sp_phone" class="fmt" size="15" maxlength="32" value="<?php echo $column["po_sp_phone"] ?>" readOnly> &nbsp;
		Fax : <input type="text" name="_sp_fax" class="fmt" size="15" maxlength="32" value="<?php echo $column["po_sp_fax"] ?>" readOnly>
		</td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_sp_address" class="fmt" style="width:100%" value="<?php echo $column["po_sp_address"] ?>" readOnly></td>
	</tr>
</table><br />
<span class="bar_bl">ITEM LIST</span>
<table width="100%" class="table_layout">
	<tr>
		<td style="width:75%">
			<table width="100%" class="table_box">
				<thead>
					<tr height="30px">
						<th width="8%">CODE</th>
						<th width="20%">ITEM</th>
						<th>DESC</th>
						<th width="12%">QTY</th>
						<th width="8%">UNIT</th>
						<th width="5%">DEL</th>
					</tr>
				</thead>
				<tbody id="rowPosition">
			<?php
			pg_result_seek($res_item,0);
			while($items =& fetchRow($res_item)) {
			?>
				<tr id="<?php echo $items[0]?>">
					<td><input type="hidden" name="_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
					<td><input type="text" name="_it_model_no[]" class="fmt" style="width:100%" value="<?php echo $items[1]?>" readonly></td>
					<td><input type="text" name="_it_desc[]" class="fmt" style="width:100%" value="<?php echo $items[2]?>" readonly></td>
					<td><input type="text" name="_plit_qty[]" class="reqn" style="width:100%" value="<?php echo number_format($items[4]) ?>" onBlur="checkAmount('<?php echo $items[0] ?>', this.value)" onKeyUp="formatNumber(this,'dot')"></td>
					<td><input type="text" name="_plit_unit[]" class="fmt" style="width:100%" value="<?php echo $items[3]?>" readonly></td>
					<td align="center"><a href="javascript:deleteItem('<?php echo $items[0]?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
				</tr>
			<?php } ?>
				</tbody>
			</table>
			<table width="100%" class="table_box">
				<tr>
					<th align="right">GRAND TOTAL</th>
					<th width="12%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
					<th width="13%">&nbsp;</th>
				</tr>
			</table>
		</td>
		<td align="left" valign="top"><a href="javascript:window.location.href='input_pl.php?_code=<?php echo $_code ?>'"><img src="../../_images/icon/reload.gif" alt="Reload original item"></a></td>
	</tr>
</table><br />
<span class="bar_bl">OTHERS</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" style="width:100%" rows="3"></textarea></td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save PL"> &nbsp; Save PL</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_pl.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel PL"> &nbsp; Cancel PL</button>
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