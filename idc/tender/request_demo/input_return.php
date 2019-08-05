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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "daily_request_demo_by_reference.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS FORM
require_once APP_DIR . "_include/request_demo/tpl_process_form.php";

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT *, ".ZKP_SQL."_getTurnDemo(use_code) AS return_demo FROM ".ZKP_SQL."_tb_using_demo WHERE use_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

$sql_item = "
SELECT
  it_code,
  it_model_no,
  it_desc,
  CASE 
	WHEN usit_returnable is true THEN 'Yes'
	WHEN usit_returnable is false THEN 'No'
  END AS it_returnable,
  CASE 
	WHEN usit_returnable is true THEN 0
	WHEN usit_returnable is false THEN 1
  END AS it_return,
  usit_qty,
  usit_remark
FROM ".ZKP_SQL."_tb_using_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '$_code' ORDER BY it_code";
$res_item = query($sql_item);

$sql_ed ="
SELECT
  it_code,
  it_model_no,
  to_char(used_expired_date, 'Mon-YYYY') AS expired_date,
  used_qty
FROM ".ZKP_SQL."_tb_using_demo_ed JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '$_code' ORDER BY it_code";
$res_ed = query($sql_ed);
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
	if (window.rowPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save return?")) {
			o.submit();
		}
	}
}

function deleteItem(idx) {
	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	updateAmount();
}

function updateAmount() {

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countWH		= window.rowPosition.rows.length;;
	var numInputWH	= 2;
	var idx_qty		= 7;		/////
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] INPUT RETURN DEMO</h3>
<?php if($column['return_demo']!= '') { ?>
<table width="100%" class="table_box">
	<tr>
		<th width="13%"><img src="../../_images/icon/hint.gif"> &nbsp; <span style="font-family:Courier;color:blue;font-weight:bold">HINT</span></th>
		<th align="left">
			<span style="font-family:Courier;font-size:12px">
			This request already has return. Please check again in <a href="detail_request.php?_code=<?php echo $_code ?>" target="_blank" style="color:#446FBE"><u>request detail</u>.</a><br />
			Current return for this request : <b style="color:#000000"><?php echo $column['return_demo'] ?></b>
			</span>
		</th>
	</tr>
</table><br />
<?php } ?>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value="insert_return">
<input type='hidden' name='_use_code' value="<?php echo $column['use_code'] ?>">
<input type='hidden' name='_dept' value="<?php echo $department ?>">
<input type='hidden' name='_cus_code' value="<?php echo $column['use_cus_to'] ?>">
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="4"><strong class="info">REQUEST INFORMATION</strong></td>
		<td colspan="3" align="right"><i>Last updated request by : <?php echo $column['use_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['use_lastupdated_timestamp']))?></i></td>
	</tr>
	<tr>
		<th width="15%">REQUEST NO</th>
		<td colspan="2"><strong><?php echo $column['use_code'] ?></strong></td>
		<th width="15%">REQUEST BY</th>
		<td width="20%"><?php echo $column['use_request_by'] ?></td>
		<th width="15%">REQUEST DATE</th>
		<td><?php echo date('d-M-Y', strtotime($column['use_request_date'])) ?></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER/<br />EVENT</th>
		<th width="12%">CODE</th>
		<td width="10%"><?php echo $column['use_cus_to'] ?></td>
		<th>NAME</th>
		<td colspan="3"><?php echo $column['use_cus_name'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="5"><?php echo $column['use_cus_address'] ?></td>
	</tr>
</table><br />
<strong class="info">RETURN INFORMATION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="15%">RETURN NO</th>
		<td width="23%"></td>
		<th width="15%">RETURN BY</th>
		<td width="20%"><input type="text" name="_return_by" class="req" size="15" value=""></td>
		<th width="15%">RETURN DATE</th>
		<td><input type="text" name="_return_date" class="reqd" size="15" value="<?php echo date('d-M-Y') ?>"></td>
	</tr>
</table><br />
<strong class="info">ITEM LIST</strong>
<table width="80%" class="table_box">
	<thead>
		<tr height="40px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php while($items =& fetchRow($res_item)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_it_code[]" value="<?php echo $items[0] ?>"></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td><input type="text" name="_it_qty[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$items[5],2)?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
			<td align="center"><a href="javascript:deleteItem('<?php echo trim($items[0]) ?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
		</tr>
<?php } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th>&nbsp;</th>
	</tr>
</table><br />
<strong class="info">OTHERS</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">SIGN BY</th>
		<td><input type="text" name="_sign_by" class="req" size="15" value=""></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td><textarea name="_remark" rows="5" style="width:100%"></textarea></td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save return demo"> &nbsp; Save return</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="detail_request.php?_code=<?php echo $_code ?>"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel return demo"> &nbsp; Cancel billing</button>
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