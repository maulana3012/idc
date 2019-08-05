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
$left_loc = 'daily_summary_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS FORM
require_once APP_DIR . "_include/other/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
//dt
$sql	= "SELECT *,(SELECT out_idx FROM ".ZKP_SQL."_tb_outgoing WHERE out_doc_ref='$_code' AND out_doc_type=3) AS out_idx FROM ".ZKP_SQL."_tb_dt WHERE dt_code = '$_code'";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

//[WAREHOUSE] dt item
$whitem_sql = "
SELECT
  it_code,					--0
  it_model_no,				--1
  it_desc,					--2
  otst_type,				--3
  otst_qty AS dt_qty,		--4
  ".ZKP_SQL."_getRDTQty('$_code', it_code, otst_type) AS return_qty		--current rt qty
FROM
  ".ZKP_SQL."_tb_outgoing_stock AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE out_idx = {$column['out_idx']}
ORDER BY it_code";
$whitem_res	=& query($whitem_sql);

//[CUSTOMER] dt item
$cusitem_sql = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.dtit_qty,			--3
  b.dtit_remark 		--4
FROM
  ".ZKP_SQL."_tb_dt_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE dt_code = '$_code'
ORDER BY it_code";
$cusitem_res	=& query($cusitem_sql);
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
function checkform(o) {
	if (o.totalComingQty.value <= 0) {
		alert("Return DT should be has at least 1 return qty");
		return;
	}

	if (window.itemCusPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRowI		= window.itemCusPosition.rows(i);
		var count_item	= 0;
		for (var j=0; j<count; j++) {
			var oRowII	= window.itemCusPosition.rows(j);
			if (oRowI.id == oRowII.id) {
				count_item += 1;
			}
		}
		if(count_item > 1) {
			alert("Please check customer item list!\n"+ count_item +" rows for item "+ oRowII.id);
			return;
		}
	}

	if (verify(o)) {
		if(confirm("Are you sure to save Return DT?")) {
			o.submit();
		}
	}
}

function fillCustomer(target) {
	if (target == 'customer') {
		keyword = window.document.frmInsert._cus_to.value;
	} else if (target == 'ship') {
		keyword = window.document.frmInsert._ship_to.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'../../_include/other/p_list_cus_code.php?_dept=<?php echo $department ?>&_check_code='+ keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;

	wSearchItem = window.open('../../_include/other/p_list_item.php?_cus_code=<?php echo $column['dt_cus_to'] ?>','wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
function createItem() {

	var f2 = wSearchItem.document.frmCreateItem;
	var oTR = window.document.createElement("TR");

	var oTD = new Array();
	var oTextbox = new Array();

	//If you add more cell
	// 1. increase tthe count as number of td
	// 2. add Case
	for (var i=0; i<6; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i]			= window.document.createElement("INPUT");
		oTextbox[i].type	= "text";

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText		= f2.elements[0].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_cus_it_code[]";
				oTextbox[i].value		= f2.elements[0].value;
				break;

			case 1: // MODEL NO
				oTD[i].innerText		= f2.elements[3].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_cus_it_model_no[]";
				oTextbox[i].value		= f2.elements[3].value;
				break;

			case 2: // DESCRIPTION
				oTD[i].innerText		= f2.elements[4].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_cus_it_desc[]";
				oTextbox[i].value		= f2.elements[4].value;
				break;

			case 3: // QTY
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].name		= "_cus_it_qty[]";
				oTextbox[i].value		= removecomma(f2.elements[5].value);
				oTextbox[i].onblur		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;


			case 4: // REMARK
				oTextbox[i]				= window.document.createElement("INPUT");
				oTextbox[i].type		= "text";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_cus_it_remark[]";
				oTextbox[i].value		= f2.elements[6].value;
				break;

			case 5: // DELETE
				oTD[i].innerHTML		= "<a href=\"javascript:deleteCusItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align			= "center";
				break;
		}

		if (i!= 5) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);

	}
	window.itemCusPosition.appendChild(oTR);

	for (var i=0; i<7; i++) {f2.elements[i].value = '';}
	updateAmount();
}


function deleteCusItem(idx) {
	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemCusPosition.removeChild(oRow);
			count = count - 1;
		}
	}
	updateAmount();
}

function checkQty(idx) {
	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var count		= window.itemWHPosition.rows.length;
	var numInput	= 6;
	var idx_qty		= 16;	/////
	var idx_return_qty		= idx_qty+1;
	var idx_remain_qty		= idx_qty+2;
	var sumOfQty		= 0;
	var sumOfComingQty	= 0;
	var sumOfReturnQty	= 0;

	for (var i = 0; i< count; i++) {
		if(idx == i) {
			var qty			= parseFloat(removecomma(e(idx_qty+i*numInput).value));
			var	coming_qty	= parseFloat(removecomma(e(idx_return_qty+i*numInput).value));
			var remain_qty	= parseFloat(removecomma(e(idx_remain_qty+i*numInput).value));

			if(coming_qty > remain_qty) {
				alert("Return qty can't more than Remain DT qty");
				e(idx_return_qty+i*numInput).value = numFormatval(remain_qty+'',2);
				return;
			}

			e(idx_remain_qty+i*numInput).value = numFormatval(qty-coming_qty+'',2);

		}
	}
	updateAmount();
}

//Reculate Amount base on the form element
function updateAmount(){
	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var numItemI	= window.itemWHPosition.rows.length;
	var numItemII	= window.itemCusPosition.rows.length;
	var numInputI	= 6;
	var numInputII	= 5;
	var idx_qty		= 16;	/////
	var idx_return_qty		= idx_qty+1;
	var idx_remain_qty		= idx_qty+2;
	var idx_qty2			= idx_qty+(numInputI*numItemI)+4;
	var sumOfQty		= 0;
	var sumOfQty2		= 0;
	var sumOfComingQty	= 0;
	var sumOfReturnQty	= 0;

	for (var i = 0; i< numItemI; i++) {
		var qty			= parseFloat(removecomma(e(idx_qty+i*numInputI).value));
		var coming_qty	= parseFloat(removecomma(e(idx_return_qty+i*numInputI).value));
		var remain_qty	= parseFloat(removecomma(e(idx_remain_qty+i*numInputI).value));
		sumOfQty		+= qty;
		sumOfComingQty	+= coming_qty;
		sumOfReturnQty	+= remain_qty;
	}

	for (var i = 0; i < numItemII; i++) {
		var qty	  = parseFloat(removecomma(e(idx_qty2+i*numInputII).value));
		sumOfQty2	+= qty;
	}

	f.totalQty.value	  	= numFormatval(sumOfQty+'',2);
	f.totalCusQty.value		= numFormatval(sumOfQty2+'',2);
	f.totalComingQty.value	= numFormatval(sumOfComingQty+'',2);
	f.totalRemainQty.value	= numFormatval(sumOfReturnQty+'',2);
}

function initPage(){

	updateAmount();
/*
	if(window.document.frmInsert.totalRemainQty.value <= 0) {
		window.location.href='detail_dt.php?_code=<?php echo $_code ?>';
	}*/
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] INPUT RETURN DO TEMPORARY</strong><br /><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value="insert_rdt">
<input type='hidden' name='_dept' value="<?php echo strtoupper(substr($currentDept,0,1)) ?>">
<input type='hidden' name='_dt_code' value="<?php echo $column['dt_code'] ?>">
<input type='hidden' name='_dt_date' value="<?php echo $column['dt_date'] ?>">
<input type='hidden' name='_type_item' value="<?php echo $column["dt_type_item"] ?>">
<strong class="info">RETURN INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">RETURN NO</th>
		<td width="30%"></td>
		<th width="12%">RETURN DATE</th>
		<td><input type="text" name="_date" class="reqd" size="15" value="<?php echo date('j-M-Y') ?>"></td>
	</tr>
	<tr>
		<th>ISSUED BY</th>
		<td><input name="_issued_by" type="text" class="req" size="15" maxlength="32" value="<?php echo $S->getValue("ma_account") ?>"></td>
		<th>TYPE INVOICE</th>
		<td>
			<input type="radio" name="_vat" value="1" disabled <?php echo ($column["dt_type_item"]==1)?'checked':''?>> Vat &nbsp;
			<input type="radio" name="_vat" value="2" disabled <?php echo ($column["dt_type_item"]==2)?'checked':''?>> Non Vat &nbsp;
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="12%">CUSTOMER</th>
		<th width="10%"><a href="javascript:fillCustomer('customer')">CODE</a></th>
		<td width="20%"><input type="text" name="_cus_to" class="req" size="5" value="<?php echo $column['dt_cus_to'] ?>"></td>
		<th width="12%">NAME</th>
		<td><input type="text" name="_cus_name" class="fmt" style="width:100%" value="<?php echo $column['dt_cus_name'] ?>"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt"  style="width:100%" maxlength="255" value="<?php echo $column['dt_cus_address'] ?>"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><a href="javascript:fillCustomer('ship')">CODE</a></th>
		<td><input type="text" name="_ship_to" class="req" size="5" value="<?php echo $column['dt_ship_to'] ?>"></td>
		<th>NAME</th>
		<td><input type="text" name="_ship_name" class="fmt" style="width:100%" value="<?php echo $column['dt_ship_name'] ?>"></td>
	</tr>
	<tr>
		<th>DT NO</th>
		<th>CODE</th>
		<td><b><a href="detail_dt.php?_code=<?php echo $column['dt_code'] ?>" target="_blank"><?php echo $column['dt_code'] ?></a></b></td>
		<th>DT DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column['dt_date'])) ?></td>
	</tr>
</table><br />
<strong class="info">[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong>
<table width="100%" class="table_box" cellspacing="1">
	<thead>
		<tr height="30px">
			<th width="8%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">DT QTY</th>
			<th width="5%">RETURN<br />QTY</th>
			<th width="5%">REMAIN<br />QTY</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php
$i = 0;
while($items =& fetchRow($whitem_res)) {
	$remain = $items[4]-$items[5];
	if($remain > 0) {
?>
		<tr id="<?php echo trim($items[0])?>">
			<td>
				<?php echo $items[0]?><br />
				<input type="hidden" name="_wh_it_code[]" value="<?php echo $items[0]?>">
				<input type="hidden" name="_wh_it_type[]" value="<?php echo $items[3]?>">
			</td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td><input type="text" name="_wh_it_qty[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$items[4],2)?>" readonly></td>
			<td><input type="text" name="_wh_it_coming_qty[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$remain,2)?>" onBlur="checkQty(<?php echo $i++ ?>)" onKeyUp="formatNumber(this,'dot')"></td>
			<td><input type="text" name="_wh_it_return_qty[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$remain,2)?>" readonly></td>
			<td><input type="text" name="_wh_it_remark[]" class="fmt" style="width:100%"></td>
		</tr>
<?php }} ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="6%"><input name="totalComingQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="6%"><input name="totalRemainQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="15%">&nbsp;</th>
</table><br />
<strong class="info">[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong> <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif" alt="Search item"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr height="30px">
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="15%">REMARK</th>
			<th width="5%" colspan="3">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><?php echo $items[1]?><input type="hidden" name="_cus_it_model_no[]" value="<?php echo $items[1]?>"></td>
			<td><?php echo $items[2]?><input type="hidden" name="_cus_it_desc[]" value="<?php echo $items[2]?>"></td>
			<td><input type="text" name="_cus_it_qty[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$items[3])?>" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()"></td>
			<td><input type="text" name="_cus_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[4]?>"></td>
			<td align="center"><a href="javascript:deleteCusItem('<?php echo trim($items[0])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalCusQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%">&nbsp;</th>
	</tr>
</table><br />
<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2">ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" size="6" class="fmt"></td>
		<td>Freight charge : Rp <input type="text" name="_delivery_freight_charge" class="fmtn" onKeyUp="formatNumber(this,'dot')" size="8" onBlur="updateAmount()"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="4"><textarea name="_remark" style="width:100%" rows="4"></textarea></td>
	</tr>
</table><br />
<input type='hidden' name='_ordered_by' value="<?php echo $cboFilter[1][ZKP_URL][0][0] ?>">
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="center">
			<button name='btnSave' class='input_btn' style='width:130px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save return DT"> &nbsp; Save return DT</button>&nbsp;
			<button name='btnCancel' class='input_btn' style='width:130px;' onclick='window.location.href="detail_dt.php?_code=<?php echo $_code ?>"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel return DT"> &nbsp; Cancel return DT</button>
		</td>
	</tr>
</table>
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