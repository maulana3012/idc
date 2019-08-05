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
$left_loc = "daily_request_demo_by_request.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS FORM
require_once APP_DIR . "_include/demo/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
//request
$sql = "
SELECT
  *,
  (SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=6) AS book_idx,
  (SELECT book_is_revised FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=6) AS book_is_revised
FROM ".ZKP_SQL."_tb_request WHERE req_code = '$_code'";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_request_demo_by_reference.php");
} else if($column['req_cfm_wh_delivery_by_account'] == '') {
	// Harus DO yang Sudah di confirm gudang
	$message = new ZKError(
		"HAS_NOT_CONFIRMED_BY_WAREHOUSE",
		"HAS_NOT_CONFIRMED_BY_WAREHOUSE",
		"Request demo no $_code has not been confirmed by warehouse. Please check again");
	$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".urlencode($_code));
} else if($column['req_cfm_marketing_timestamp'] != '') {
	// Harus DO yang belum di confirm request stock
	$message = new ZKError(
		"ALREADY_CONFIRMED_BY_MARKETING",
		"ALREADY_CONFIRMED_BY_MARKETING",
		"Request demo no $_code already confirmed by marketing and input to demo stock. To revised item or qty, please unconfirm first.");
	$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".urlencode($_code));
} else if($column['book_is_revised'] == 't') {
	// Bukan DO yang dalam proses revisi
	$message = new ZKError(
		"ERROR_STATUS_REVISED",
		"ERROR_STATUS_REVISED",
		"Dokumen no $_code status is revised. You cannot change item or qty before the document confirm by warehouse");
	$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".urlencode($_code));
}

//[WAREHOUSE] request item
$whitem_sql = "
SELECT
  it_code,		--0
  it_model_no,		--1
  it_desc,		--2
  rqit_qty,		--3
  rqit_remark,		--4
  rqit_type		--5
FROM
  ".ZKP_SQL."_tb_request_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE req_code = '$_code'
ORDER BY it_code";
$whitem_res	=& query($whitem_sql);
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
//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 300) / 2;
	wSearchItem = window.open(
		'./p_list_item.php','wSearchItem',
		'scrollbars,width=550,height=300,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
//Please see the p_list_item.php
function createItem() {

	var f2		= wSearchItem.document.frmCreateItem;
	var oTR		= window.document.createElement("TR");
	var oTD		= new Array();
	var oTextbox	= new Array();

	//check same item in WAREHOUSE list
	var count = itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.itemWHPosition.rows(i);
		if (oRow.id == trim(f2.elements[3].value)) {
			alert(
				"Please check item list"+
				"\nItem ["+ trim(f2.elements[3].value) +"] " + f2.elements[4].value +  " already exist!");
			return false;
		}
	}

	//Print cell for WH
	for (var i=0; i<6; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i]		 = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // _wh_it_code
				oTD[i].innerText			= trim(f2.elements[3].value);
				oTextbox[i].type			= "hidden";
				oTextbox[i].name			= "_wh_it_code[]";
				oTextbox[i].value			= f2.elements[3].value;
				break;

			case 1: // _wh_it_model_no
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_wh_it_model_no[]";
				oTextbox[i].value			= f2.elements[4].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 2: // _wh_it_desc
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_wh_it_desc[]";
				oTextbox[i].value			= f2.elements[5].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 3: // _wh_it_qty
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className		= "reqn";
				oTextbox[i].name			= "_wh_it_qty[]";
				oTextbox[i].value			= numFormatval(f2.elements[8].value+'',2);
				oTextbox[i].onblur			= function() {updateAmount();}
				oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 4: // _wh_it_remark
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_wh_it_remark[]";
				oTextbox[i].value			= f2.elements[9].value;
				break;

			case 5: // DELETE
				oTD[i].innerHTML			= "<a href=\"javascript:deleteWHItem('" + trim(f2.elements[1].value)+ "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align				= "center";
				break;
		}

		if (i!=5) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[1].value);
		oTR.appendChild(oTD[i]);
	}
	window.itemWHPosition.appendChild(oTR);
	updateAmount();
}

//Delete Item rows collection
function deleteWHItem(idx) {
	var count = window.itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.itemWHPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemWHPosition.removeChild(oRow);
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
	var countWH		= window.itemWHPosition.rows.length;;
	var numInputWH	= 5;
	var idx_qty		= 9;	/////
	var sumOfQty	= 0;

	for (var i=0; i<countWH; i++) {
		var qty = parseFloat(removecomma(e((idx_qty)+i*numInputWH).value));
		sumOfQty	+= qty;
	}
	f.totalWhQty.value	  = numFormatval(sumOfQty + '', 2);
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE REQUEST DEMO UNIT STOCK</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value='<?php echo $column['req_code'] ?>'>
<input type='hidden' name='_book_idx' value='<?php echo $column['book_idx'] ?>'>
<input type='hidden' name='_revision_time' value='<?php echo $column['req_revesion_time'] ?>'>
<strong class="info">REQUEST INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REQUEST NO</th>
		<td width="20%"><span class="bar"><?php echo $column["req_code"] ?></span></td>
		<th width="15%">ISSUED BY</th>
		<td width="34%"><input name="_issued_by" type="text" class="req" size="15" maxlength="32" value="<?php echo $column["req_issued_by"] ?>"></td>
		<th width="15%">ISSUED DATE</th>
		<td><input name="_issued_date" type="text" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column["req_issued_date"])) ?>"></td>
	</tr>
</table><br />
<strong class="info">[<font color="#315c87">WAREHOUSE</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr height="35px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="15%">REMARK</th>
			<th width="5%" colspan="2">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_wh_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><input type="text" name="_wh_it_model_no[]" style="width:100%" class="fmt" value="<?php echo $items[1]?>"></td>
			<td><input type="text" name="_wh_it_desc[]" style="width:100%" class="fmt" value="<?php echo $items[2]?>"></td>
			<td align="right"><input type="text" name="_wh_it_qty[]" class="reqn" style="width:100%" value="<?php echo number_format($items[3],2)?>" readonly></td>
			<td><input type="text" name="_wh_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[4]?>"></td>
			<td align="center" colspan="2">
				<a href="javascript:deleteWHItem('<?php echo trim($items[0])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="3" width="20%">&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" rows="5" style="width:100%"><?php echo $column["req_remark"] ?></textarea></td>
	</tr>
</table>
<?php
//[WAREHOUSE] outgoing item
$outitem_sql = "
SELECT trim(it_code), otst_qty
FROM
  ".ZKP_SQL."_tb_outgoing_v2
  JOIN ".ZKP_SQL."_tb_outgoing_stock_v2 USING(out_idx)
WHERE out_doc_ref = '".trim($_code)."'
ORDER BY it_code";
$outitem_res =& query($outitem_sql);

while($items =& fetchRow($outitem_res)) {
	$out_item[0][] = $items[0];
	$out_item[1][] = $items[1];
	echo "<input type=\"hidden\" name=\"_out_it_code[]\" value=\"". $items[0]. "\">";
	echo "<input type=\"hidden\" name=\"_out_it_qty[]\" value=\"". $items[1]. "\">\n";
}
?>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update request"> &nbsp; Update</button>&nbsp;
			<button name='btnCancel' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Back to Detail</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnUpdate.onclick = function() {
		if (window.itemWHPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}

		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_request_revised';
				oForm.submit();
			}
		}
	}

	window.document.all.btnCancel.onclick = function() {
		window.location.href = 'revise_request.php?_code=<?php echo $_code ?>';
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